<?php

namespace Compass\Thread;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\Locale;
use Compass\Conversation\Domain_Search_Entity_ThreadMessage_Task_Reindex;
use Compass\Conversation\Domain_Search_Entity_ThreadMessage_Task_Delete;
use Compass\Conversation\Domain_Search_Entity_ThreadMessage_Task_Hide;
use CompassApp\Domain\Member\Entity\Permission;
use JetBrains\PhpStorm\ArrayShape;

/**
 * хелпер для всего, что связано с тредами
 */
class Helper_Threads {

	protected const _MAX_QUOTED_MESSAGES_COUNT                      = 150;      // количество процитированных сообщений
	protected const _MAX_REPOSTED_OR_QUOTED_MESSAGES_COUNT_IN_CHUNK = 15;       // число процетированных сообщений в одном родительском
	protected const _MESSAGE_TYPE_EMPTY                             = 0;        // тип пустого сообщения

	/**
	 * проверяет что к треду есть доступ - если есть возвращает мету треда
	 *
	 * @param string $thread_map
	 * @param int    $user_id
	 * @param bool   $is_need_unfollow_if_no_access
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_UserNotMember
	 * @throws CaseException
	 */
	public static function getMetaIfUserMember(string $thread_map, int $user_id, bool $is_need_unfollow_if_no_access = true):array {

		// получаем мету треда и map родительской сущности
		$meta_row = Type_Thread_Meta::getOne($thread_map);

		try {

			// проверяем доступ пользователя, если мета сущность - диалог
			$result = self::_getMetaIfUserMemberForConversation($meta_row, $thread_map, $user_id, $is_need_unfollow_if_no_access);
		} catch (Domain_Thread_Exception_Guest_AttemptInitialThread $e) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "guest attempt to initial thread");
		}

		return $result;
	}

	/**
	 * получение меты, если пользователь является участником меты сущности диалога
	 *
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_UserNotMember
	 * @throws \parseException
	 */
	protected static function _getMetaIfUserMemberForConversation(array $meta_row, string $thread_map, int $user_id, bool $is_need_unfollow_if_no_access):array {

		// пробуем отдать результат из кэша без socket запросов
		$cache = self::_getCache($meta_row["source_parent_rel"]);
		if (in_array($user_id, $cache["user_list"])) {

			// проверяем имеет ли наш пользователь доступ к сообщению-родителю треда
			$is_set_readonly_false = self::_checkUserHaveAccessToParentEntity($meta_row, $user_id, $is_need_unfollow_if_no_access);

			// обновляем пользователей в мете треда если необходимо
			return self::_updateThreadMetaUsersIfNeed($thread_map, $meta_row, $cache["parent_meta_users"], $is_set_readonly_false);
		}

		// выбрасываем исключение, если наш пользователь находится в списке not_found_user_list в кэше для диалога
		self::_throwIfUserInNotFoundCache($cache["not_found_user_list"], $user_id);

		// обращаемся к родительской мете и обновляем юзеров
		try {
			$parent_meta_users = Type_Thread_Rel_Meta::getUsers($meta_row, $user_id);
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {

			// ВНИМАНИЕ exception cs_Conversation_IsBlockedOrDisabled выбьется только в single диалоге,
			// поэтому в будущем мы спокойной можем использовать meta_row, которая хранится в объекте exception $e->getMetaRow()
			Type_Thread_Meta::setIsReadOnly($thread_map, true);
			throw $e;
		}

		// проверяем что у пользователя есть доступ к родительской сущности
		$is_set_readonly_false = self::_checkUserHaveAccessToParentEntity($meta_row, $user_id, $is_need_unfollow_if_no_access);

		// обновляем мету и если пользователя нет в обновленных пользователях - обновляем кэш и плюем экзепшен
		$meta_row = self::_updateThreadMetaUsersIfNeed($thread_map, $meta_row, $parent_meta_users, $is_set_readonly_false);
		if (!isset($meta_row["users"][$user_id])) {

			self::_updateCache($meta_row["source_parent_rel"], $cache, $user_id, $parent_meta_users, false);
			throw new cs_Thread_UserNotMember();
		}

		self::_updateCache($meta_row["source_parent_rel"], $cache, $user_id, $parent_meta_users, true);

		return $meta_row;
	}

	// получаем кэш пользователей
	protected static function _getCache(array $source_parent_rel):array {

		$source_parent_map = Type_Thread_SourceParentRel::getMap($source_parent_rel);
		return Type_Thread_Rel_Meta::getCache($source_parent_map);
	}

	// проверяет что пользователь есть в кэше ненайденных пользователей
	protected static function _throwIfUserInNotFoundCache(array $not_found_user_list, int $user_id):void {

		if (in_array($user_id, $not_found_user_list)) {
			throw new cs_Thread_UserNotMember();
		}
	}

	// проверяет что к тредам есть доступ - возвращает список мет доступных тредов и список thread_map недоступных
	#[ArrayShape(["allowed_meta_list" => "array|mixed", "not_allowed_thread_map_list" => "array|mixed"])]
	public static function getMetaListIfUserMember(array $thread_map_list, int $user_id, bool $is_need_unfollow_if_no_access = true):array {

		// достаем список мет тредов, сгруппированный по map сущности source_parent_rel, за которым закреплены треды
		[$meta_list_by_source_parent_rel, $meta_list_for_hire_requests] = self::getMetaListGroupedByParentMetaMap($thread_map_list);

		$allowed_meta_list           = [];
		$not_allowed_thread_map_list = [];

		// пробуем получить меты тредов, закрепленные за диалогом
		if (count($meta_list_by_source_parent_rel) > 0) {

			[$allowed_meta_list, $not_allowed_thread_map_list] = self::_getMetaListIfUserMemberForConversation(
				$meta_list_by_source_parent_rel,
				$user_id,
				$is_need_unfollow_if_no_access);
		}

		// пробуем получить меты тредов, закрепленные за заявками найма/увольнения
		if (count($meta_list_for_hire_requests) > 0) {

			[$meta_list_for_hire_requests, $not_allowed_thread_map_list] = Domain_Thread_Action_GetMetaForHireRequests::do(
				$user_id, $meta_list_for_hire_requests, $not_allowed_thread_map_list
			);
			$allowed_meta_list = array_merge($allowed_meta_list, $meta_list_for_hire_requests);
		}

		// возвращаем ответ
		return [
			"allowed_meta_list"           => $allowed_meta_list,
			"not_allowed_thread_map_list" => $not_allowed_thread_map_list,
		];
	}

	// достаем список мет тредов, сгруппированный по мете родителя, за которым они закреплены
	public static function getMetaListGroupedByParentMetaMap(array $thread_map_list):array {

		// получаем список мет запрошенных тредов
		$meta_list = Type_Thread_Meta::getAll($thread_map_list);

		// раскидываем меты тредов в зависимости от типа родителя
		// (треды для заявок отдельно от тредов для сообщений диалога и других типов)
		$meta_list_by_source_parent_rel = [];
		$meta_list_for_hire_requests    = [];
		foreach ($meta_list as $v) {

			$parent_rel_type = Type_Thread_ParentRel::getType($v["parent_rel"]);
			switch ($parent_rel_type) {

				// если это заявка найма/увольнения, то собираем отдельный список
				case PARENT_ENTITY_TYPE_HIRING_REQUEST:
				case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:

					$meta_list_for_hire_requests[$v["thread_map"]] = $v;
					break;

				// для всех остальных типов
				default:

					// достаем map сущности source_parent_rel, за которым закреплен тред
					$parent_meta_map = Type_Thread_SourceParentRel::getMap($v["source_parent_rel"]);

					// создаем список мет, сгруппированный по source_parent_rel
					$meta_list_by_source_parent_rel[$parent_meta_map][] = $v;
			}
		}

		return [$meta_list_by_source_parent_rel, $meta_list_for_hire_requests];
	}

	/**
	 * получаем меты тредов, закрепленные за обычным диалогом
	 *
	 */
	protected static function _getMetaListIfUserMemberForConversation(array $meta_list_by_source_parent_rel, int $user_id, bool $is_need_unfollow_if_no_access):array {

		/*
		 * проходимся по списку мет и пробуем получить из кэша информацию о сущностях source_parent_rel, за которыми закреплены треды
		 *
		 * cache_list_by_source_parent_rel	        - (array) массив данных из кэша, сгруппированный по map source_parent_rel тредов
		 * not_cache_meta_list_by_source_parent_rel - (array) массив мет тех тредов, данные о source_parent_rel которых не нашлось в кэше
		 */
		[$cache_list_by_source_parent_rel, $not_cache_meta_list_by_source_parent_rel] = self::_getCacheDataForParentMeta($meta_list_by_source_parent_rel);

		// проходимся по полученному из кэша списку и выполняем необходимые действия с данными из кэша
		$data = self::_doWorkToFoundCacheList($cache_list_by_source_parent_rel, $meta_list_by_source_parent_rel, $user_id, $is_need_unfollow_if_no_access);

		/*
		 * $data = [
		 * 	allowed_meta_list						  - (array) массив доступных для пользователя мет тредов
		 * 	not_allowed_thread_map_list				  - (array) массив map тредов, доступ к которым отсутствует у пользователя
		 * 	not_found_in_cache_meta_list_by_source_parent_rel - (array) массив мета, если пользователь не был найден в кэше по родителю
		 * ]
		 */
		$allowed_meta_list                                 = $data["allowed_meta_list"];
		$not_allowed_thread_map_list                       = $data["not_allowed_thread_map_list"];
		$not_found_in_cache_meta_list_by_source_parent_rel = $data["not_found_in_cache_meta_list_by_source_parent_rel"];

		// мерджим спис
		$not_cache_meta_list_by_source_parent_rel = array_merge($not_cache_meta_list_by_source_parent_rel, $not_found_in_cache_meta_list_by_source_parent_rel);

		// работаем с теми тредами, информации о сущности source_parent_rel которых не нашлось в кэше
		$data = self::_doWorkIfNotFoundInCache($not_cache_meta_list_by_source_parent_rel, $user_id, $is_need_unfollow_if_no_access);

		$allowed_meta_list           = array_merge($allowed_meta_list, $data["allowed_meta_list"]);
		$not_allowed_thread_map_list = array_merge($not_allowed_thread_map_list, $data["not_allowed_thread_map_list"]);

		return [$allowed_meta_list, array_unique($not_allowed_thread_map_list)];
	}

	// получаем данные кэша для сущностей source_parent_rel тредов
	protected static function _getCacheDataForParentMeta(array $meta_list_by_source_parent_rel):array {

		// проходимся по списку мет и пробуем получить из кэша информацию о сущностях source_parent_rel, за которыми закреплены треды
		$cache_list_by_source_parent_rel          = [];
		$not_cache_meta_list_by_source_parent_rel = [];
		foreach ($meta_list_by_source_parent_rel as $parent_meta_map => $source_parent_rel) {

			// ищем в кэше информацию о сущностей source_parent_rel
			$cache = Type_Thread_Rel_Meta::getCache($parent_meta_map);

			// если кэша нет, то добавляем в отдельный список
			if (count($cache["user_list"]) < 1 && count($cache["not_found_user_list"]) < 1) {

				// привязываем список мет к сущности source_parent_rel, информацию по которой не нашли в кэше
				$not_cache_meta_list_by_source_parent_rel[$parent_meta_map] = $source_parent_rel;
			}

			$cache_list_by_source_parent_rel[$parent_meta_map] = $cache;
		}

		return [$cache_list_by_source_parent_rel, $not_cache_meta_list_by_source_parent_rel];
	}

	// выполняем необходимые действия с данными из кэша
	protected static function _doWorkToFoundCacheList(array $cache_list_by_source_parent_rel, array $meta_list_by_conversation, int $user_id, bool $is_need_unfollow_if_no_access):array {

		$allowed_meta_list                                 = [];
		$not_allowed_thread_map_list                       = [];
		$not_found_in_cache_meta_list_by_source_parent_rel = [];

		// проходимся по полученному из кэша списку
		foreach ($cache_list_by_source_parent_rel as $parent_meta_map => $cache) {

			// достаем список мет тредов, закрепленных за сущностью source_parent_rel
			$meta_list = $meta_list_by_conversation[$parent_meta_map];

			// если наш пользователь есть в списке пользователей, полученном из кэша
			if (in_array($user_id, $cache["user_list"])) {

				// проверяем доступ к родительской сущности треда и обновляем пользователей треда если необходимо
				$data = self::_checkAccessToParentEntityAndUpdateUsersIfNeed($cache, $meta_list, $user_id, $is_need_unfollow_if_no_access);

				// список доступных мет тредов мерджим с предыдущим результатом
				$allowed_meta_list = array_merge($allowed_meta_list, $data["allowed_meta_list"]);

				// полученный список недоступных для пользователя тредов мерджим с предыдущим результатом
				$not_allowed_thread_map_list = array_merge($not_allowed_thread_map_list, $data["not_allowed_thread_map_list"]);
			}

			// если же пользователь находится в списке not_found_user_list, полученный из кэша
			if (in_array($user_id, $cache["not_found_user_list"])) {

				// добавляем map тредов текущей сущности в список недоступных для пользователя
				$not_allowed_thread_map_list = self::_addThreadMapListToNotAllowedList($meta_list, $not_allowed_thread_map_list);
			}

			// если наш пользователь не нашелся в кэше ни в одном ни в другом списке, то добавляем в отдельный список
			if (!in_array($user_id, $cache["not_found_user_list"]) && !in_array($user_id, $cache["user_list"])) {
				$not_found_in_cache_meta_list_by_source_parent_rel[$parent_meta_map] = $meta_list;
			}
		}

		return [
			"allowed_meta_list"                                 => $allowed_meta_list,
			"not_allowed_thread_map_list"                       => $not_allowed_thread_map_list,
			"not_found_in_cache_meta_list_by_source_parent_rel" => $not_found_in_cache_meta_list_by_source_parent_rel,
		];
	}

	// проверяем доступ к родительской сущности треда и обновляем пользователей треда если необходимо
	protected static function _checkAccessToParentEntityAndUpdateUsersIfNeed(array $cache, array $meta_list, int $user_id, bool $is_need_unfollow_if_no_access):array {

		// достаем данные сущности source_parent_rel из меты одного из треда
		$source_parent_rel = $meta_list[0]["source_parent_rel"];

		// проверяем доступ к родительской сущности треда (parent_rel)
		$data = self::_checkUserHaveAccessToParentEntityList($meta_list, $source_parent_rel, $user_id, $is_need_unfollow_if_no_access);

		$not_allowed_thread_map_list = $data["not_allowed_thread_map_list"];

		// проходимся по доступным для пользователя метам треда
		$allowed_meta_list = [];
		foreach ($data["allowed_meta_list"] as $meta_row) {

			// добавляем обновленную мету треда в список доступных
			$allowed_meta_list[] = self::_updateThreadMetaUsersIfNeed(
				$meta_row["thread_map"],
				$meta_row,
				$cache["parent_meta_users"],
				in_array($meta_row["thread_map"], $data["set_readonly_false_thread_map_list"]));
		}

		return [
			"allowed_meta_list"           => $allowed_meta_list,
			"not_allowed_thread_map_list" => $not_allowed_thread_map_list,
		];
	}

	// проверяем имеет ли наш пользователь доступ к сообщению-родителю треда
	protected static function _checkUserHaveAccessToParentEntityList(array $meta_list, array $source_parent_rel, int $user_id, bool $is_need_unfollow_if_no_access):array {

		// достаем данные dynamic сущности source_parent_rel, за которым закреплены треды
		[$source_parent_rel_dynamic] = Type_Thread_SourceParentDynamic::get($source_parent_rel);

		// получаем тип meta сущности
		$parent_meta_type = Type_Thread_SourceParentRel::getType($source_parent_rel);

		// проверяем доступ к родителю треда (parent_rel), получаем список доступных мет и список map недоступных тредов
		$data = self::_getDataAfterCheckUserHaveAccessParentEntity($meta_list, $parent_meta_type, $source_parent_rel_dynamic, $user_id);

		$allowed_meta_list                  = $data["allowed_meta_list"];
		$not_allowed_thread_map_list        = $data["not_allowed_thread_map_list"];
		$set_readonly_false_thread_map_list = $data["set_readonly_false_thread_map_list"];

		// отписываем пользователя от списка тредов
		if ($is_need_unfollow_if_no_access) {
			Type_Phphooker_Main::doUnfollowThreadList($not_allowed_thread_map_list, $user_id);
		}

		return [
			"allowed_meta_list"                  => $allowed_meta_list,
			"not_allowed_thread_map_list"        => $not_allowed_thread_map_list,
			"set_readonly_false_thread_map_list" => $set_readonly_false_thread_map_list,
		];
	}

	// получаем данные после проверки доступа пользоватея к сообщению-родителю треда
	protected static function _getDataAfterCheckUserHaveAccessParentEntity(array $meta_list, int $parent_meta_type, Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic, int $user_id):array {

		$allowed_meta_list                  = [];
		$not_allowed_thread_map_list        = [];
		$set_readonly_false_thread_map_list = [];

		// для каждого треда
		foreach ($meta_list as $meta_row) {

			$access_user_data = self::_getAccessDataOnParentMetaType($meta_row, $parent_meta_type, $source_parent_rel_dynamic, $user_id);

			$is_user_have_access   = $access_user_data["is_user_have_access"];
			$is_set_readonly_false = $access_user_data["is_set_readonly_false"];

			// если у пользователя нет доступа, то добавляем мету в список недоступных для пользователя
			if (!$is_user_have_access) {

				$not_allowed_thread_map_list[] = $meta_row["thread_map"];
				continue;
			}

			// если тред нужно обновить как readonly = 0
			if ($is_set_readonly_false) {
				$set_readonly_false_thread_map_list[] = $meta_row["thread_map"];
			}

			// добавляем map треда в список доступных для пользователя
			$allowed_meta_list[] = $meta_row;
		}

		return [
			"allowed_meta_list"                  => $allowed_meta_list,
			"not_allowed_thread_map_list"        => $not_allowed_thread_map_list,
			"set_readonly_false_thread_map_list" => $set_readonly_false_thread_map_list,
		];
	}

	// получаем данные доступа пользователя в зависимости от типа meta сущности
	#[ArrayShape(["is_user_have_access" => "bool", "is_set_readonly_false" => "bool"])]
	protected static function _getAccessDataOnParentMetaType(array $meta_row, int $parent_meta_type, Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic, int $user_id):array {

		// в зависимости от типа meta сущности
		switch ($parent_meta_type) {

			case SOURCE_PARENT_ENTITY_TYPE_CONVERSATION:

				try {

					$access_data = self::getAccessDataIfParentConversation($meta_row, $source_parent_rel_dynamic, $user_id);

					// получаем флаг имеет ли пользователь доступ к сообщению-родителю и нужно ли обновлять readonly треда
					$is_user_have_access   = $access_data["is_user_have_access"];
					$is_set_readonly_false = $access_data["is_set_readonly_false"];
				} catch (cs_Message_IsDeleted) {

					$is_user_have_access   = false;
					$is_set_readonly_false = false;
				}

				break;

			default:
				throw new ParseFatalException("Unhandled meta relationship type in method: " . __METHOD__);
		}

		return [
			"is_user_have_access"   => $is_user_have_access,
			"is_set_readonly_false" => $is_set_readonly_false,
		];
	}

	// добавляем map тредов в список недоступных для пользователя
	protected static function _addThreadMapListToNotAllowedList(array $meta_list, array $not_allowed_thread_map_list):array {

		foreach ($meta_list as $v) {

			$thread_map = $v["thread_map"];

			// пропускаем если такой тред уже есть среди списка недоступных
			if (in_array($thread_map, $not_allowed_thread_map_list)) {
				continue;
			}

			// иначе добавляем в список
			$not_allowed_thread_map_list[] = $thread_map;
		}

		return $not_allowed_thread_map_list;
	}

	// выполняем необходимые действия с метами тредов, данные о source_parent_rel которых не нашлось в кэше
	protected static function _doWorkIfNotFoundInCache(array $not_cache_meta_list_by_source_parent_rel, int $user_id, bool $is_need_unfollow_if_no_access):array {

		// пробуем получить пользователей у всех сущностей source_parent_rel, данные о которых не нашли в кэше
		$data_packet = Type_Thread_Rel_Meta::getUsersForMetaList($not_cache_meta_list_by_source_parent_rel, $user_id);

		// получаем список пользователей, сортированных по треду
		$user_list_by_thread_map = $data_packet["users_by_thread_map"];

		// список мет тредов, к сущностям source_parent_rel которых у пользователя не оказалось доступа
		$not_access_meta_list = $data_packet["not_access_parent_meta_list"];

		// ВНИМАНИЕ "not access" можно получить только в single диалоге,
		// поэтому мы можем спокойно отдать меты недоступных тредов, так как пользователь всегда участник single диалога
		$allowed_meta_list = $not_access_meta_list;

		// треды диалогов, что недоступны для пользователя, помечаем как read_only
		self::_setReadOnlyForNotAccessConversation($not_access_meta_list);

		// полученных пользователей тех сущностей source_parent_rel, которых не оказалось в кэше, мерджим с пользователями треда
		$not_allowed_thread_map_list = [];
		foreach ($not_cache_meta_list_by_source_parent_rel as $parent_meta_map => $meta_list) {

			// проверяем доступ к родителю треда
			$source_parent_rel = $meta_list[0]["source_parent_rel"];
			$data              = self::_checkUserHaveAccessToParentEntityList($meta_list, $source_parent_rel, $user_id, $is_need_unfollow_if_no_access);

			$not_allowed_thread_map_list = array_merge($not_allowed_thread_map_list, $data["not_allowed_thread_map_list"]);
			$allowed_meta_list           = array_merge($allowed_meta_list, $data["allowed_meta_list"]);

			// добавляем пользователей сущности source_parent_rel к пользователям мет тредов и обновляем кэш диалога, если необходимо
			$data = self::_updateUsersAndCacheForMetaListIfNeed(
				$meta_list,
				$user_list_by_thread_map,
				$parent_meta_map,
				$user_id,
				$data["set_readonly_false_thread_map_list"]);

			$not_allowed_thread_map_list = array_merge($not_allowed_thread_map_list, $data["not_allowed_thread_map_list"]);
		}

		return [
			"allowed_meta_list"           => $allowed_meta_list,
			"not_allowed_thread_map_list" => $not_allowed_thread_map_list,
		];
	}

	// треды диалогов, что недоступны для пользователя, помечаем как read_only
	protected static function _setReadOnlyForNotAccessConversation(array $not_access_meta_list):void {

		$thread_map_list = [];
		foreach ($not_access_meta_list as $meta_row) {
			$thread_map_list[] = $meta_row["thread_map"];
		}

		if (count($thread_map_list) < 1) {
			return;
		}

		// для тех пользователей, что заблокированы (собеседником или системой), помечаем тред is_read_only = 1
		Type_Thread_Meta::setListIsReadOnly($thread_map_list, true);
	}

	/**
	 * обновляем пользователей списка мет и кэш, если необходимо
	 *
	 * @param array  $meta_list
	 * @param array  $user_list_by_thread_map
	 * @param string $parent_meta_map
	 * @param int    $user_id
	 * @param array  $set_readonly_false_thread_map_list
	 *
	 * @return array[]
	 * @long
	 */
	#[ArrayShape(["meta_list" => "array", "not_allowed_thread_map_list" => "array"])]
	protected static function _updateUsersAndCacheForMetaListIfNeed(array $meta_list, array $user_list_by_thread_map, string $parent_meta_map, int $user_id, array $set_readonly_false_thread_map_list):array {

		// получаем схему для сохранения в кэш данных диалога
		$cache = Type_Thread_Rel_Meta::getCache($parent_meta_map);

		$allowed_meta_list           = [];
		$not_allowed_thread_map_list = [];
		foreach ($meta_list as $meta_row) {

			$thread_map = $meta_row["thread_map"];

			// если для этого треда не получили пользователей диалога, то пропускаем
			if (!isset($user_list_by_thread_map[$thread_map])) {
				continue;
			}

			// обновляем пользователей меты если необходимо
			$parent_meta_users = $user_list_by_thread_map[$thread_map];
			$meta_row          = self::_updateThreadMetaUsersIfNeed(
				$thread_map,
				$meta_row,
				$parent_meta_users,
				in_array($meta_row["thread_map"], $set_readonly_false_thread_map_list));

			// если по итогу оказалось, что нашего пользователя нет в списке пользователей меты треда,
			// то обновляем кэш, добавляя пользователя в список not_found_user_list, и добавляем map треда в список недоступных
			if (!isset($meta_row["users"][$user_id])) {

				self::_updateCache($meta_row["source_parent_rel"], $cache, $user_id, $parent_meta_users, false);
				$not_allowed_thread_map_list[] = $meta_row["thread_map"];
				continue;
			}

			// иначе добавляем пользователя в кэш в список user_list
			self::_updateCache($meta_row["source_parent_rel"], $cache, $user_id, $parent_meta_users, true);

			$allowed_meta_list[] = $meta_row;
		}

		return [
			"meta_list"                   => $allowed_meta_list,
			"not_allowed_thread_map_list" => $not_allowed_thread_map_list,
		];
	}

	/**
	 * скрываем список сообщений в треде
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function hideMessageList(string $thread_map, array $meta_row, array $message_map_list, int $user_id):void {

		$message_map_list_grouped_by_block_id = [];
		foreach ($message_map_list as $v) {

			$block_id                                          = \CompassApp\Pack\Message\Thread::getBlockId($v);
			$message_map_list_grouped_by_block_id[$block_id][] = $v;
		}

		// скрываем сообщения
		$message_list = [];
		foreach ($message_map_list_grouped_by_block_id as $k => $v) {

			$temp_message_list = Type_Thread_Message_Block::hideMessageList($v, $user_id, $thread_map, $k);
			$message_list      = array_merge($message_list, $temp_message_list);
		}

		// увеличим счетчик скрытых сообщений в мете треда для пользователя если были новые сообщения среди скрытых
		self::_doActionAfterHideMessage($message_list, $thread_map, $user_id);

		$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
		$threads_updated_version = Gateway_Socket_Conversation::updateThreadsUpdatedData($parent_conversation_map);

		// отправляем событие скрытия сообщения
		$talking_user_item = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);
		Gateway_Bus_Sender::threadMessageListHidden([$talking_user_item], $thread_map, $message_map_list, $parent_conversation_map, $threads_updated_version);

		// отправляем сообщение на повторную индексацию
		Domain_Search_Entity_ThreadMessage_Task_Hide::queueList($message_map_list, [$user_id]);
	}

	/**
	 * если были новые скрытые сообщения
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	protected static function _doActionAfterHideMessage(array $message_list, string $thread_map, int $user_id):void {

		if (count($message_list) < 1) {
			return;
		}

		$meta_row = Type_Thread_Meta::incCountHiddenMessage($thread_map, $user_id, count($message_list));

		// если пользователь скрыл все сообщения в треде, то отписываем его от треда и убираем из избранного
		if (Type_Thread_Meta_Utils::isAllMessagesHidden($meta_row, $user_id)) {

			// убираем тред из избранного если он там был
			Domain_Thread_Action_RemoveFromFavorite::do($user_id, $thread_map);

			// отписываем от треда
			Domain_Thread_Action_Follower_Unfollow::do($user_id, $thread_map, true);

			$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
			Helper_Threads::hideThread($user_id, $thread_map, $parent_conversation_map);
		}

		// скрываем превью
		Type_Thread_Message_Block::onHideMessageListWithPreview($user_id, $message_list);

		// если были скрыты файла
		Type_Thread_Message_Block::onHideMessageListWithFile($meta_row, $message_list, $user_id);
	}

	// редактируем текст сообщения
	// @long
	public static function editMessageText(string $thread_map, array $meta_row, string $message_map, int $user_id, string $new_text, array $mention_user_id_list, array $follower_row):array {

		self::_throwIfThreadIsReadOnly($meta_row);

		// проверяем что тред не locked / readonly
		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);
		self::_throwIfThreadIsLocked($dynamic_obj);

		// обновляем сообщение в треде
		[$edited_message, $diff_added_mentioned_user_id_list, $diff_removed_mentioned_user_id_list] = Type_Thread_Message_Block::editMessageText(
			$message_map,
			$user_id,
			$new_text,
			$meta_row["users"],
			$mention_user_id_list
		);

		$new_mentioned_user_id_list = [];
		foreach ($diff_added_mentioned_user_id_list as $k) {

			if (Type_Thread_Message_Main::getHandler($edited_message)::isMessageHiddenForUser($edited_message, $k)) {
				continue;
			}

			// если упомянутый отправитель или отредактировавший то им не нужны уведомления
			if ($k == $user_id || $k == Type_Thread_Message_Main::getHandler($edited_message)::getSenderUserId($edited_message)) {
				continue;
			}

			if (Type_Thread_Meta_Users::isMember($k, $meta_row["users"])) {
				$new_mentioned_user_id_list[] = $k;
			}
		}

		// отправляем задачу на обновление пользовательских данных
		Type_Phphooker_Main::updateUserDataForMentionedOnMessageEdit($thread_map, $new_mentioned_user_id_list, $diff_removed_mentioned_user_id_list);

		// другие действия
		$prepared_message = self::_onMessageEdited(
			$message_map,
			$meta_row,
			$edited_message,
			$new_mentioned_user_id_list,
			$mention_user_id_list,
			$diff_added_mentioned_user_id_list,
			$follower_row);

		// обрабатываем ссылки в тексте, если они есть
		Type_Preview_Producer::addTaskIfLinkExist(
			$user_id, $new_text, $edited_message["message_map"], $meta_row["users"], Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"])
		);

		// удаляем сообщение из поиска для пользователя
		Domain_Search_Entity_ThreadMessage_Task_Reindex::queueList([$edited_message], Locale::getLocale());

		// если нужно отмечаем время получения сообщения (если добавили меншен)
		$edited_at               = floor(Type_Thread_Message_Main::getHandler($edited_message)::getLastMessageTextEditedAt($edited_message) / 1000);
		$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
		Domain_Thread_Action_Message_UpdateConversationAnswerState::doByEditMessage($parent_conversation_map, $meta_row["source_parent_rel"], $user_id,
			$new_mentioned_user_id_list, $edited_at);

		return $prepared_message;
	}

	/**
	 * выполняется при редактировании сообщения в треде
	 *
	 * @param string $thread_map
	 * @param string $message_map
	 * @param array  $meta_row
	 * @param array  $message
	 * @param array  $new_mentioned_user_id_list
	 * @param array  $mention_user_id_list
	 * @param array  $diff_mentioned_user_id_list
	 * @param array  $follower_row
	 *
	 * @return array
	 * @throws \parseException
	 * @long
	 */
	protected static function _onMessageEdited(string $message_map, array $meta_row, array $message, array $new_mentioned_user_id_list, array $mention_user_id_list, array $diff_mentioned_user_id_list, array $follower_row):array {

		$talking_user_list = [];
		foreach ($meta_row["users"] as $k => $_) {

			// если пользователь по каким-то причинам не может получить это сообщение
			if (Type_Thread_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $k)) {
				continue;
			}

			// добавляем пользователя в список, кому нужно отправить событие
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($k, false, in_array($k, $new_mentioned_user_id_list));
		}

		// формируем пуш-уведомление и объект с сообщением треда
		[$source_parent_rel_dynamic] = Type_Thread_SourceParentDynamic::get($meta_row["source_parent_rel"]);

		$location_type = $source_parent_rel_dynamic->location_type;
		$push_data     = Domain_Thread_Entity_Push::makePushData($message, $meta_row, $location_type);

		// достаем фолловеров
		$follower_list = Type_Thread_Followers::getFollowerUsersDiff($follower_row);

		$last_message_text_edited_at = Type_Thread_Message_Main::getHandler($message)::getLastMessageTextEditedAt($message);
		$new_text                    = Type_Thread_Message_Main::getHandler($message)::getText($message);
		$prepared_message            = self::_prepareEditedMessageFormat($message);
		Gateway_Bus_Sender::threadMessageEdited(
			$talking_user_list,
			$prepared_message,
			$message_map,
			$new_text,
			$last_message_text_edited_at,
			$mention_user_id_list,
			$diff_mentioned_user_id_list,
			$push_data,
			$follower_list,
			$location_type);
		return $prepared_message;
	}

	// подготавливаем измененное сообщение по новому формату
	protected static function _prepareEditedMessageFormat(array $edited_message):array {

		// получаем массив с количеством реакций
		$message_map = Type_Thread_Message_Main::getHandler($edited_message)::getMessageMap($edited_message);
		$thread_map  = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		$block_id    = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

		[$reaction_list, $reaction_last_edited_at] = Type_Thread_Reaction_Main::getReactionListAndUpdated($thread_map, $block_id, $message_map);

		return Type_Thread_Message_Main::getHandler($edited_message)::prepareForFormat($edited_message, $reaction_list, $reaction_last_edited_at);
	}

	// удаляем несколько сообщений
	public static function deleteMessageList(int $user_id, string $thread_map, array $message_map_list, array $meta_row,
							     bool $is_new_try_delete_message_error = false, bool $is_forced = false):array {

		self::_throwIfThreadIsReadOnly($meta_row);

		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);
		$users       = $meta_row["users"];

		// проверяем, что блок активный и доступен для написания сообщений
		self::_throwIfThreadIsLocked($dynamic_obj);

		$message_map_list_grouped_by_block_id = self::_groupMessageMapListByBlockId($message_map_list, $dynamic_obj);

		// проходимся по всем сообщениям сгруппированным по block_id, удаляем сообщения и добавляем все удаленные сообщения в массив
		$message_list = [];
		foreach ($message_map_list_grouped_by_block_id as $k1 => $v1) {

			$temp_message_list = Type_Thread_Message_Block::deleteMessageList(
				$v1, $thread_map, $k1, $user_id, $users, $is_new_try_delete_message_error, $is_forced);

			foreach ($temp_message_list as $message_map => $message) {
				$message_list[$message_map] = $message;
			}
		}

		// отправляем ws ивент об успешном удалении сообщений
		self::_sendEventAboutDeletedMessageList($thread_map, $message_map_list, $users);

		// удаляем превью
		Type_Thread_Message_Block::onDeleteMessageListWithPreview(array_keys($message_list));

		// удаляем файлы
		Type_Thread_Message_Block::onDeleteMessageListWithFile($meta_row, $message_list);

		// удаляем сообщение из поиска для всех пользователей
		Domain_Search_Entity_ThreadMessage_Task_Delete::queueList($message_map_list);

		// возвращаем последнее удаленные сообщения
		return $message_list;
	}

	// группируем сообщения по блоку
	protected static function _groupMessageMapListByBlockId(array $message_map_list, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj):array {

		$message_map_list_grouped_by_block_id = [];

		// группируем сообщения по id блока
		foreach ($message_map_list as $v) {

			$block_id                                          = \CompassApp\Pack\Message\Thread::getBlockId($v);
			$message_map_list_grouped_by_block_id[$block_id][] = $v;
		}

		return $message_map_list_grouped_by_block_id;
	}

	/**
	 * Скрыть тред у пользователя
	 *
	 * @throws \parseException
	 */
	public static function hideThread(int $user_id, string $thread_map, string $parent_conversation_map):void {

		Type_Thread_Dynamic::addHideUser($user_id, $thread_map);
		Gateway_Socket_Conversation::hideThreadForUser($user_id, $thread_map, $parent_conversation_map);
	}

	// mute/unmute треда
	public static function setMuted(int $user_id, string $thread_map, bool $is_muted):void {

		// добавляем/обновляем user_mute_info
		Type_Thread_Dynamic::setIsMuted($thread_map, $user_id, $is_muted);

		// устанавливаем флаг is_muted
		Type_Thread_Menu::setIsMuted($user_id, $thread_map, $is_muted);

		// отправляем событие участнику
		Gateway_Bus_Sender::threadIsMutedChanged($user_id, $thread_map, $is_muted);
	}

	// отписаться от тредов по его родителю
	public static function unfollowThreadListByMetaMap(int $user_id, string $source_parent_map):void {

		// отписываем пользователя от всех тредов по map родителя
		$thread_menu_list = Type_Thread_Menu::setUnfollowByMetaMap($user_id, $source_parent_map);

		// отправляем задачу на актуализацию follower_list
		Type_Thread_Menu::sendTaskIfUnfollowThreadList($user_id, $thread_menu_list);
	}

	/**
	 * отписываем пользователя от тредов при смене роли на обычного сотрудника
	 *
	 */
	public static function unfollowThreadListIfRoleChangeToEmployee(int $user_id, string $source_parent_map):void {

		// отписываем пользователя от всех тредов по map родителя
		$thread_menu_list = Type_Thread_Menu::setUnfollowIfRoleChangeToEmployee($user_id, $source_parent_map);

		// отправляем задачу на актуализацию follower_list
		Type_Thread_Menu::sendTaskIfUnfollowThreadList($user_id, $thread_menu_list);
	}

	/**
	 * создаём цитату V2
	 *
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_MessageList_IsEmpty
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	public static function addQuoteV2(string $thread_map, array $meta_row, array $message_map_list, string $client_message_id, int $user_id, string $text, array $mention_user_id_list, array $parent_message = [], string $platform = Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM):array {

		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);

		// формируем массивы со всеми процитированными сообщениями
		[$chunk_quote_message_list] = self::getChunkMessageList($message_map_list, $dynamic_obj, $parent_message, $user_id);

		// если список сообщений для цитирования пуст, то выдаем exception
		self::_throwIfMessageListIsEmpty($chunk_quote_message_list);

		// собираем список сообщений для цитирования
		$quote_list = [];
		foreach ($chunk_quote_message_list as $k => $quote_message_list) {

			// текст должен быть только у последнего сообщения - у остальных убираем
			$message_text = $k == count($chunk_quote_message_list) - 1 ? $text : "";

			// формируем цитату
			$quote        = Type_Thread_Message_Main::getLastVersionHandler()::makeMassQuote(
				$user_id,
				$message_text,
				$client_message_id . "_" . "{$k}",
				$quote_message_list,
				$platform);
			$quote_list[] = Type_Thread_Message_Main::getHandler($quote)::addMentionUserIdList($quote, $mention_user_id_list);
		}

		// цитируем сообщения
		return Domain_Thread_Action_Message_AddList::do($thread_map, $meta_row, $quote_list);
	}

	// получаем отчанкованный список сообщений для репоста/цитаты
	public static function getChunkMessageList(array $message_map_list, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, array $parent_message, int $user_id):array {

		$block_list = [];
		if (count($message_map_list) > 0) {
			$block_list = self::_getBlockListRow($message_map_list, $dynamic_obj);
		}

		// получаем отчанкованные массивы со всеми сообщениями
		return array_values(self::_getChunkMessageListForRepostOrQuoteV2($message_map_list, $block_list, $user_id, $parent_message));
	}

	// получаем отчанкованные массивы со всеми сообщениями
	protected static function _getChunkMessageListForRepostOrQuoteV2(array $message_map_list, array $block_list, int $user_id, array $parent_message):array {

		$message_list = [];

		// если имеется родитель
		if (count($parent_message) > 0) {

			// создаем новую структуру сообщения треда для сообщения диалога
			$parent_message                         = Type_Thread_Message_Main::getLastVersionHandler()::makeStructureForConversationMessage($parent_message);
			$parent_message["thread_message_index"] = 0;

			// подготавливаем сообщение к цитированию/репосту
			$message_list = self::_getPreparedMessageListForRepostOrQuote($parent_message, $message_list);
		}

		return self::_getPreparedRepostedOrQuotedChunkMessageListV2($message_list, $message_map_list, $block_list, $user_id);
	}

	// получаем отчанкованный для цитирования и репоста список сообщений
	protected static function _getPreparedRepostedOrQuotedChunkMessageListV2(array $message_list, array $message_map_list, array $block_list, int $user_id):array {

		// получаем сообщения
		foreach ($message_map_list as $v) {

			try {
				$message = self::_getMessageForRepostOrQuote($v, $block_list, $user_id);
			} catch (cs_Message_IsDeleted) {
				continue;
			}

			// получаем подготовленный список сообщений
			$message_list = self::_getPreparedMessageListForRepostOrQuote($message, $message_list);
		}

		// чанкуем сообщения для цитаты
		$chunk_message_list = array_chunk($message_list, self::_MAX_REPOSTED_OR_QUOTED_MESSAGES_COUNT_IN_CHUNK);

		// подготавливаем чанки с сообщениями
		$prepare_chunk_data_message_list = self::_prepareChunkRepostAndQuoteMessageList($chunk_message_list);

		// подготавливаем списки сообщений с цитатами
		return [self::_prepareRepostOrQuoteMessageList($prepare_chunk_data_message_list), count($message_list)];
	}

	// подготавливаем сообщения для цитирования
	protected static function _getPreparedMessageListForRepostOrQuote(array $message, array $message_list):array {

		// для нумерации каждого сообщения
		$index = count($message_list) + 1;

		if (\CompassApp\Pack\Message::isFromThread($message["message_map"])) {
			return self::_getPreparedMessageListForRepostOrQuoteIfFromThread($message, $message_list, $index);
		}

		return self::_getPreparedMessageListForRepostOrQuoteIfFromConversation($message, $message_list, $index);
	}

	// подготавливаем сообщения для цитаты/репоста если сообщение из треда
	protected static function _getPreparedMessageListForRepostOrQuoteIfFromThread(array $message, array $prepared_message_list, int $index):array {

		$message_type = Type_Thread_Message_Main::getHandler($message)::getType($message);

		switch ($message_type) {

			case THREAD_MESSAGE_TYPE_FILE:

				// устанавливаем новый file_uid и компануем сообщение
				$message                 = Type_Thread_Message_Main::getHandler($message)::setNewFileUid($message);
				$prepared_message_list[] = self::_makeOutputPreparedMessageList($message, $message_type, $message);
				return $prepared_message_list;

			case THREAD_MESSAGE_TYPE_QUOTE:
			case THREAD_MESSAGE_TYPE_MASS_QUOTE:
				return self::_prepareForRepostOrQuoteIfMessageQuoteV2($message, $message_type, $prepared_message_list, $index);
			case THREAD_MESSAGE_TYPE_REPOST:
				return self::_prepareForRepostOrQuoteIfMessageRepostV2($message, $message_type, $prepared_message_list, $index);
			default:
				$prepared_message_list[] = self::_makeOutputPreparedMessageList($message, $message_type, $message);
				return $prepared_message_list;
		}
	}

	// подготавливаем сообщения для цитаты/репоста если сообщение из диалога
	protected static function _getPreparedMessageListForRepostOrQuoteIfFromConversation(array $message, array $prepared_message_list, int $index):array {

		$message_type = Type_Thread_Message_Main::getHandler($message)::getType($message);
		switch ($message_type) {

			case THREAD_MESSAGE_TYPE_CONVERSATION_FILE:

				// устанавливаем новый file_uid и компануем сообщение
				$message                 = Type_Thread_Message_Main::getHandler($message)::setNewFileUid($message);
				$prepared_message_list[] = self::_makeOutputPreparedMessageList($message, $message_type, $message);
				return $prepared_message_list;

			case THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE:
				return self::_prepareForRepostOrQuoteIfMessageQuoteV2($message, $message_type, $prepared_message_list, $index);
			case THREAD_MESSAGE_TYPE_CONVERSATION_REPOST:
				return self::_prepareForRepostOrQuoteIfMessageRepostV2($message, $message_type, $prepared_message_list, $index);
			default:
				$prepared_message_list[] = self::_makeOutputPreparedMessageList($message, $message_type, $message);
				return $prepared_message_list;
		}
	}

	// подготавливаем сообщение-цитату
	protected static function _prepareForRepostOrQuoteIfMessageQuoteV2(array $message, int $message_type, array $prepared_message_list, int $index):array {

		// получаем все процитированные сообщения
		$quoted_message_list = Type_Thread_Message_Main::getHandler($message)::getQuotedMessageList($message);

		$quoted_message_list = self::_upgradeMessageListV2($quoted_message_list, $message, $index);

		// компануем сообщения
		foreach ($quoted_message_list as $_ => $quoted_message) {
			$prepared_message_list[] = self::_makeOutputPreparedMessageList($quoted_message, $message_type, $message);
		}

		return $prepared_message_list;
	}

	// подготавливаем сообщение-репост
	protected static function _prepareForRepostOrQuoteIfMessageRepostV2(array $message, int $message_type, array $prepared_message_list, int $index):array {

		// получаем все репостнутые сообщения
		$reposted_message_list = Type_Thread_Message_Main::getHandler($message)::getRepostedMessageList($message);

		$reposted_message_list = self::_upgradeMessageListV2($reposted_message_list, $message, $index);

		// компануем сообщения
		foreach ($reposted_message_list as $_ => $reposted_message) {
			$prepared_message_list[] = self::_makeOutputPreparedMessageList($reposted_message, $message_type, $message);
		}

		return $prepared_message_list;
	}

	// обновляем список сообщений репоста/цитаты
	protected static function _upgradeMessageListV2(array $message_list, array $message, int $index):array {

		// меняем список сообщений цитаты, если среди них нашлось сообщение типа repost, file or quote
		$message_list = self::_doActionIfIssetFileOrQuote($message_list, $index);

		// если текст отсутствует, то просто убираем его из сообщений
		$message_list = Type_Thread_Message_Handler_Default::removeEmptyMessageFromMessageList($message_list);

		// если репост подписан текстом, добавляем сообщение пустышку, для правильного чанкования
		return self::_addEmptyMessageIfExistTextRepostOrQuote($message_list, $message);
	}

	// подготавливаем чанки с репостами/цитатами
	public static function _prepareChunkRepostAndQuoteMessageList(array $chunk_message_list):array {

		// компануем по первоначальным сообщениям составляющим чанку
		$prepare_chunk_data_message_list = [];
		foreach ($chunk_message_list as $k1 => $message_list) {

			// счетчик родительских сообщений
			$iterate_parent_message = 0;

			foreach ($message_list as $k2 => $message) {

				// пропускаем ранее добавленные сообщения пустышки
				if ($message["message"]["type"] === self::_MESSAGE_TYPE_EMPTY) {
					continue;
				}

				$prepare_chunk_data_message_list[$k1][$iterate_parent_message]["type"]              = $message["type"];
				$prepare_chunk_data_message_list[$k1][$iterate_parent_message]["message_list"][$k2] = $message["message"];
				$prepare_chunk_data_message_list[$k1][$iterate_parent_message]["parent_message"]    = $message["parent_message"];

				// убираем комментарий у пачки сообщений, если они были под общим родителем, но оказались в разных чанках
				if ($k1 != 0) {

					$previous_message_list = $chunk_message_list[$k1 - 1];
					$previous_last_message = array_pop($previous_message_list);

					// если нужно, убираем текст
					if ($previous_last_message["parent_message"] == $message["parent_message"]) {
						$prepare_chunk_data_message_list[$k1][$iterate_parent_message]["parent_message"]["data"]["text"] = "";
					}
				}

				if (!isset($message_list[$k2 + 1]["parent_message"])) {
					continue;
				}

				if ($message["parent_message"] != $message_list[$k2 + 1]["parent_message"]) {
					$iterate_parent_message++;
				}
			}
		}

		return $prepare_chunk_data_message_list;
	}

	// подготавливаем списки сообщений для репоста/цитирование
	public static function _prepareRepostOrQuoteMessageList(array $prepare_chunk_data_message_list):array {

		// подготавливаем сообщения
		$prepared_chunk_message_list = [];
		foreach ($prepare_chunk_data_message_list as $k1 => $chunk_data_message_list) {

			foreach ($chunk_data_message_list as $data_message_list) {

				$prepared_chunk_message_list = Type_Thread_Message_Main::getHandler($data_message_list["parent_message"])::makeRepostedOrQuotedMessageList(
					$data_message_list,
					$prepared_chunk_message_list,
					$k1);
			}
		}

		return $prepared_chunk_message_list;
	}

	// создаем цитату
	public static function addQuote(string $thread_map, array $meta_row, array $message_map_list, int $user_id, string $text, string $client_message_id, array $mention_user_id_list, array $parent_message = [], string $platform = Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM):array {

		$block_list = [];
		if (count($message_map_list) > 0) {

			$dynamic_obj = Type_Thread_Dynamic::get($thread_map);
			$block_list  = self::_getBlockListRow($message_map_list, $dynamic_obj);
		}

		// пробуем сформировать массив со всеми процитированными сообщениями
		$quoted_message_list = self::_getQuotedMessageList($message_map_list, $block_list, $user_id, $parent_message);

		// если список сообщений для цитирования пуст, то выдаем exception
		self::_throwIfMessageListIsEmpty($quoted_message_list);

		// формируем цитату
		$quote = Type_Thread_Message_Main::getLastVersionHandler()::makeMassQuote($user_id, $text, $client_message_id, $quoted_message_list, $platform);
		$quote = Type_Thread_Message_Main::getHandler($quote)::addMentionUserIdList($quote, $mention_user_id_list);

		// добавляем сообщение в тред
		return Domain_Thread_Action_Message_Add::do($thread_map, $meta_row, $quote);
	}

	// получаем массив сообщений для цитирования
	protected static function _getQuotedMessageList(array $message_map_list, array $block_list, int $user_id, array $parent_message):array {

		$message_count       = 0;
		$quoted_message_list = [];

		// если имеется родитель
		if (count($parent_message) > 0) {

			// создаем новую структуру сообщения треда для сообщения диалога
			$parent_message                         = Type_Thread_Message_Main::getLastVersionHandler()::makeStructureForConversationMessage($parent_message);
			$parent_message["thread_message_index"] = 0;

			// подготавливаем сообщение к цитированию
			$quoted_message        = self::prepareMessageForQuoteRemind($parent_message);
			$quoted_message_list[] = $quoted_message;

			// инкрементим кол-во выбранных для цитирования сообщений
			$message_count++;
			$message_count = self::_incSelectedMessageCountIfRepostOrQuote($message_count, $quoted_message);
		}

		return self::_getPreparedQuotedMessageList($quoted_message_list, $message_map_list, $block_list, $user_id, $message_count);
	}

	// получаем готовый для цитирования список сообщений
	protected static function _getPreparedQuotedMessageList(array $quoted_message_list, array $message_map_list, array $block_list, int $user_id, int $message_count):array {

		// проходимся по всем сообщениями, который выбрали для цитирования
		foreach ($message_map_list as $v) {

			try {
				$quoted_message = self::_getMessageForRepostOrQuote($v, $block_list, $user_id);
			} catch (cs_Message_IsDeleted) {
				continue;
			}

			// подготавливаем сообщение к цитированию и добавляем в массив к остальным
			$quoted_message        = self::prepareMessageForQuoteRemind($quoted_message);
			$quoted_message_list[] = $quoted_message;

			// подсчитываем количество выбранных для цитирования сообщений;
			$message_count++;

			// подсчитываем количество сообщений репоста/цитаты
			$message_count = self::_incSelectedMessageCountIfRepostOrQuote($message_count, $quoted_message);

			// отдаем exception если превысили лимит
			self::_throwIfExceededSelectedMessageLimit($message_count);
		}

		return $quoted_message_list;
	}

	// подготавливаем сообщения для цитирования/напоминания
	public static function prepareMessageForQuoteRemind(array $message):array {

		if (\CompassApp\Pack\Message::isFromThread($message["message_map"])) {
			return self::_prepareMessageForQuoteRemindIfFromThread($message);
		}

		return self::_prepareMessageForQuoteRemindIfFromConversation($message);
	}

	// подготавливаем сообщение для цитаты/напоминания если сообщение из треда
	// @long - switch..case по типу сообщения
	protected static function _prepareMessageForQuoteRemindIfFromThread(array $message):array {

		$message_type = Type_Thread_Message_Main::getHandler($message)::getType($message);
		switch ($message_type) {

			case THREAD_MESSAGE_TYPE_FILE:

				// устанавливаем новый file_uid
				$message = Type_Thread_Message_Main::getHandler($message)::setNewFileUid($message);
				break;

			case THREAD_MESSAGE_TYPE_QUOTE:
			case THREAD_MESSAGE_TYPE_MASS_QUOTE:

				// получаем все сообщения цитаты
				$quoted_message_list = Type_Thread_Message_Main::getHandler($message)::getQuotedMessageList($message);

				// меняем список сообщений цитаты, если среди них нашлось сообщение типа repost, file or quote
				$quoted_message_list = self::_doActionIfIssetFileOrQuote($quoted_message_list);

				// если текст отсутствует, то просто убираем его из сообщений
				$quoted_message_list = Type_Thread_Message_Handler_Default::removeEmptyMessageFromMessageList($quoted_message_list);

				// добавляем обновленный список процитированных сообщений в цитату
				$message["data"]["quoted_message_list"] = $quoted_message_list;

				break;
			case THREAD_MESSAGE_TYPE_REPOST:

				// получаем все сообщения репоста
				$reposted_message_list = Type_Thread_Message_Main::getHandler($message)::getRepostedMessageList($message);

				// меняем список сообщений репоста, если среди них нашлось сообщение типа file or quote
				$reposted_message_list = self::_doActionIfIssetFileOrQuote($reposted_message_list);

				// если текст отсутствует, то просто убираем его из сообщений
				$reposted_message_list = Type_Thread_Message_Handler_Default::removeEmptyMessageFromMessageList($reposted_message_list);

				// добавляем обновленный список репостнутых сообщений в репост
				$message["data"]["reposted_message_list"] = $reposted_message_list;
				break;
		}

		return $message;
	}

	// подготавливаем сообщение для цитаты/напоминания если сообщение из диалога
	// @long - switch..case по типу сообщения
	protected static function _prepareMessageForQuoteRemindIfFromConversation(array $message):array {

		$message_type = Type_Thread_Message_Main::getHandler($message)::getType($message);
		switch ($message_type) {

			case THREAD_MESSAGE_TYPE_CONVERSATION_FILE:

				// устанавливаем новый file_uid
				$message = Type_Thread_Message_Main::getHandler($message)::setNewFileUid($message);
				break;

			case THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE:

				// получаем все сообщения цитаты
				$quoted_message_list = Type_Thread_Message_Main::getHandler($message)::getQuotedMessageList($message);

				// меняем список сообщений цитаты, если среди них нашлось сообщение типа repost, file or quote
				$quoted_message_list = self::_doActionIfIssetFileOrQuote($quoted_message_list);

				// если текст отсутствует, то просто убираем его из сообщений
				$quoted_message_list = Type_Thread_Message_Handler_Default::removeEmptyMessageFromMessageList($quoted_message_list);

				// добавляем обновленный список процитированных сообщений в цитату
				$message["data"]["quoted_message_list"] = $quoted_message_list;
				break;

			case THREAD_MESSAGE_TYPE_CONVERSATION_REPOST:

				// получаем все сообщения репоста
				$reposted_message_list = Type_Thread_Message_Main::getHandler($message)::getRepostedMessageList($message);

				// меняем список сообщений репоста, если среди них нашлось сообщение типа file or quote
				$reposted_message_list = self::_doActionIfIssetFileOrQuote($reposted_message_list);

				// если текст отсутствует, то просто убираем его из сообщений
				$reposted_message_list = Type_Thread_Message_Handler_Default::removeEmptyMessageFromMessageList($reposted_message_list);

				// добавляем обновленный список репостнутых сообщений в репост
				$message["data"]["reposted_message_list"] = $reposted_message_list;
				break;
		}

		return $message;
	}

	// если внутри процитированных сообщений находится файл или цитата, то выполняем определенные действия
	// @long - действия на тип сообщения (файл/цитата)
	protected static function _doActionIfIssetFileOrQuote(array $message_list, int $index = 1):array {

		foreach ($message_list as $k => $v) {

			// если среди сообщений оказался файл
			if (Type_Thread_Message_Main::getHandler($v)::isFile($v)) {

				// устанавливаем новый file_uid файлу
				$message_list[$k] = Type_Thread_Message_Main::getHandler($v)::setNewFileUid($v);

				// устанавливаем индекс для сообщений
				$message_list[$k]["thread_message_index"] = $index;
				$index++;
				continue;
			}

			// если среди сообщений оказалась цитата
			if (Type_Thread_Message_Main::getHandler($v)::isQuote($v)) {

				// подготавливаем цитату, и так как список сообщений сильно изменился, то проходимся еще раз по нему
				$message_list = self::_doPreparedMessageIfQuote($v, $k, $message_list, $index);
				return self::_doActionIfIssetFileOrQuote($message_list, $index);
			}

			// если среди сообщений оказался репост
			if (Type_Thread_Message_Main::getHandler($v)::isRepost($v)) {

				// подготавливаем репост, и так как список сообщений сильно изменился, то проходимся еще раз по нему
				$message_list = self::_doPreparedMessageIfRepost($v, $k, $message_list, $index);
				return self::_doActionIfIssetFileOrQuote($message_list, $index);
			}

			// устанавливаем индекс для сообщений
			$message_list[$k]["thread_message_index"] = $index;
			$index++;
			continue;
		}

		return $message_list;
	}

	// подготавливаем цитату
	protected static function _doPreparedMessageIfQuote(array $message, int $message_index, array $message_list, int $index):array {

		// получаем список цитированных сообщений
		$ex_quote_message_list = Type_Thread_Message_Main::getHandler($message)::getQuotedMessageList($message);;

		// превращаем комментарий цитаты в обычное сообщение
		$new_message = self::_changeQuoteToMessageText($message);

		// радостно удаляем прошлое сообщение
		unset($message_list[$message_index]);

		// и вставляем в нужное место, в зависимости от константы
		$ex_quote_message_list[] = $new_message;

		// все сообщения бывшей цитаты делаем частью списка сообщений
		return self::_setMessageListOfQuote($message_list, $ex_quote_message_list, $message_index, $index);
	}

	// устанавливаем сообщения цитаты в список сообщений
	protected static function _setMessageListOfQuote(array $main_message_list, array $added_message_list, int $parent_message_index, int $index):array {

		// берем список сообщений до индекса удаленного сообщения
		$message_list_1 = array_slice($main_message_list, 0, $parent_message_index);

		// берем список сообщений после индекса удаленного сообщения
		$message_list_2 = array_slice($main_message_list, $parent_message_index);

		// добавляем между полученными списками сообщений сообщения цитаты
		$new_message_list = array_merge($message_list_1, $added_message_list, $message_list_2);

		// устанавливаем thread_message_index для сообщений, чтобы они корректно выстраивались в цитате
		return self::_setNewThreadMessageIndex($new_message_list, $index);
	}

	// подготавливаем репост
	protected static function _doPreparedMessageIfRepost(array $message, int $message_index, array $message_list, int $index):array {

		// если у найденной цитаты имеется текст, то добавляем ее в список цитаты верхнего уровня как сообщение типа text
		$message_list = self::_addQuoteAsTextIfIssetText($message_list, $message_index, $message);

		// достаем сообщения бывшего репоста
		$ex_repost_message_list = Type_Thread_Message_Main::getHandler($message)::getRepostedMessageList($message);

		// все сообщения бывшего репоста делаем частью списка сообщений
		return self::_setMessageListOfRepost($message_list, $ex_repost_message_list, $message_index, $index);
	}

	// меняем сообщение на тип текст, если имеется текст у цитаты
	protected static function _addQuoteAsTextIfIssetText(array $message_list, int $ex_repost_index, array $ex_repost):array {

		// меняем структуру цитаты на сообщение типа text
		$new_message = self::_changeQuoteToMessageText($ex_repost);

		// ставим измененное сообщение на то же место
		$message_list[$ex_repost_index] = $new_message;

		return $message_list;
	}

	// меняем сообщение типа quote на text
	protected static function _changeQuoteToMessageText(array $message):array {

		if (\CompassApp\Pack\Message::isFromThread($message["message_map"])) {

			$new_message = Type_Thread_Message_Main::getHandler($message)::makeText(
				$message["sender_user_id"],
				$message["data"]["text"],
				$message["client_message_id"],
				$message["mention_user_id_list"],
				$message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM);
		} else {

			$new_message = Type_Thread_Message_Main::getHandler($message)::makeConversationText(
				$message["sender_user_id"],
				$message["data"]["text"],
				$message["client_message_id"],
				$message["created_at"],
				$message["mention_user_id_list"],
				$message["platform"] ?? Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM);
		}

		// добавляем дополнительные данные в новое сообщение
		$new_message["message_map"] = $message["message_map"];
		$new_message["created_at"]  = $message["created_at"];
		$new_message["updated_at"]  = $message["updated_at"];

		return $new_message;
	}

	// устанавливаем сообщения цитаты в список сообщений
	protected static function _setMessageListOfRepost(array $main_message_list, array $added_message_list, int $parent_message_index, int $index):array {

		// берем список сообщений до индекса удаленного сообщения
		$message_list_1 = array_slice($main_message_list, 0, $parent_message_index + 1);

		// берем список сообщений после индекса удаленного сообщения
		$message_list_2 = array_slice($main_message_list, $parent_message_index + 1);

		// добавляем между полученными списками сообщений сообщения цитаты
		$new_message_list = array_merge($message_list_1, $added_message_list, $message_list_2);

		// устанавливаем thread_message_index для сообщений, чтобы они корректно выстраивались в цитате
		return self::_setNewThreadMessageIndex($new_message_list, $index);
	}

	// устанавливаем новый thread_message_index для сообщений
	protected static function _setNewThreadMessageIndex(array $message_list, int $index):array {

		foreach ($message_list as $k => $v) {

			$v["thread_message_index"] = $index;
			$message_list[$k]          = $v;
			$index++;
		}

		return $message_list;
	}

	// инкрементим количество выбранных сообщений, если репост или цитата
	protected static function _incSelectedMessageCountIfRepostOrQuote(int $message_count, array $message):int {

		// добавляем количество процитированных сообщений, если сообщение является цитатой
		if (Type_Thread_Message_Main::getHandler($message)::isQuote($message)) {

			$quoted_message_list = Type_Thread_Message_Main::getHandler($message)::getQuotedMessageList($message);
			$message_count       += Type_Thread_Message_Main::getHandler($message)::getRepostedAndQuotedMessageCount($quoted_message_list);
		}

		// добавляем количество репостнутых сообщений, если сообщение является репостом
		if (Type_Thread_Message_Main::getHandler($message)::isRepost($message)) {

			$reposted_message_list = Type_Thread_Message_Main::getHandler($message)::getRepostedMessageList($message);
			$message_count         += Type_Thread_Message_Main::getHandler($message)::getRepostedAndQuotedMessageCount($reposted_message_list);
		}

		return $message_count;
	}

	// выбрасываем исключение, если превысили лимит выбранных сообщений
	protected static function _throwIfExceededSelectedMessageLimit(int $message_count):void {

		if ($message_count > self::_MAX_QUOTED_MESSAGES_COUNT) {
			throw new cs_Message_Limit();
		}
	}

	// достаем сообщение из блока для цитирования
	protected static function _getMessageForRepostOrQuote(string $message_map, array $block_list, int $user_id):array {

		// достаем id блока сообщений
		$block_id = \CompassApp\Pack\Message\Thread::getBlockId($message_map);
		self::_throwIfBlockNotExist($block_list, $block_id);

		// достаем сообщение из блока
		$quoted_message = Type_Thread_Message_Block::getMessage($message_map, $block_list[$block_id]);

		// если сообщение удалено, то пропускаем его
		if (Type_Thread_Message_Main::getHandler($quoted_message)::isMessageDeleted($quoted_message)) {
			throw new cs_Message_IsDeleted();
		}

		// если сообщение не подходит для цитирования
		self::_throwIfNotAllowToQuote($quoted_message, $user_id);

		return $quoted_message;
	}

	// выбрасываем исключение если не нашли блока
	protected static function _throwIfBlockNotExist(array $block_list, int $block_id):void {

		if (!isset($block_list[$block_id])) {
			throw new ReturnFatalException("message block not exist");
		}
	}

	/**
	 * Выбрасываем исключение если сообщение нельзя процитировать
	 *
	 * @param array $quoted_message
	 * @param int   $user_id
	 *
	 * @return void
	 * @throws ParamException
	 * @throws \parseException
	 */
	protected static function _throwIfNotAllowToQuote(array $quoted_message, int $user_id):void {

		// флаги/тип сообщения позволяют его цитировать?
		if (!Type_Thread_Message_Main::getHandler($quoted_message)::isAllowToQuote($quoted_message, $user_id)) {
			throw new ParamException(__METHOD__ . ": you have not permissions to quote this message");
		}
	}

	// выбрасываем exception, если список сообщений пуст
	protected static function _throwIfMessageListIsEmpty(array $message_list):void {

		if (count($message_list) < 1) {
			throw new cs_MessageList_IsEmpty();
		}
	}

	// метод для получения списка упомянутых из текста
	public static function getMentionUserIdListFromText(array $meta_row, string $text):array {

		$parent_meta_type = Type_Thread_SourceParentRel::getType($meta_row["source_parent_rel"]);

		if ($parent_meta_type != SOURCE_PARENT_ENTITY_TYPE_CONVERSATION) {
			return [];
		}

		$user_id_list = Domain_Thread_Entity_MentionUsers::getMentionUsersForText($text);
		if (count($user_id_list) < 1) {
			return [];
		}

		$filtered_mention_user_id_list = [];

		// проходимся по всем упомянутым пользователям
		foreach ($user_id_list as $user_id) {

			// проверяем что указанный id в string не больше значения PHP_INT_MAX
			if (self::_isNumberStringMorePhpIntMax($user_id)) {
				continue;
			}

			// если получили конкретный user_id, проверяем, что тот является участником чата
			if ($user_id > 0 && !Type_Thread_Meta_Users::isMember($user_id, $meta_row["users"])) {
				continue;
			}

			// если получили user_id = 0, то значит упомянута группа участников (@all, @badge)
			if ($user_id == 0) {

				[$_, $_, $_, $location_type] = Type_Thread_SourceParentDynamic::get($meta_row["source_parent_rel"]);

				// если тип чата не позволяет упоминать по бейджу
				if (in_array($location_type, [CONVERSATION_TYPE_SINGLE_DEFAULT, CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT, CONVERSATION_TYPE_SINGLE_NOTES, CONVERSATION_TYPE_GROUP_SUPPORT])) {
					continue;
				}

				$mention_user_id_list          = Domain_Thread_Entity_MentionUsers::getList($text, $meta_row["users"]);
				$filtered_mention_user_id_list = array_merge($mention_user_id_list, $filtered_mention_user_id_list);
				continue;
			}

			$filtered_mention_user_id_list[] = intval($user_id);
		}
		return array_unique($filtered_mention_user_id_list);
	}

	// проверяем что указанный id в string не больше значения PHP_INT_MAX
	protected static function _isNumberStringMorePhpIntMax(string $user_id):bool {

		if (mb_strlen($user_id) > 19) {
			return true;
		}

		if (mb_strlen($user_id) < 19) {
			return false;
		}

		$number = (int) mb_substr($user_id, 0, 18);
		if ($number < 922337203685477580) {
			return false;
		}

		$number = (int) mb_substr($user_id, 18);
		if ($number <= 7) {
			return false;
		}

		return true;
	}

	// прикрепляем пользователей к треду если нужно
	public static function attachUsersToThread(array $meta_row, array $user_id_list):array {

		[$source_parent_rel_dynamic] = Type_Thread_SourceParentDynamic::get($meta_row["source_parent_rel"]);

		$need_follow_user_id_list = [];
		foreach ($user_id_list as $user_id) {

			$parent_meta_type = Type_Thread_SourceParentRel::getType($meta_row["source_parent_rel"]);
			$access_user_data = self::_getAccessDataOnParentMetaType($meta_row, $parent_meta_type, $source_parent_rel_dynamic, $user_id);

			if ($access_user_data["is_user_have_access"]) {
				$need_follow_user_id_list[] = $user_id;
			}
		}

		// подписываем пользователя на тред
		return Domain_Thread_Action_Follower_Follow::do($need_follow_user_id_list, $meta_row["thread_map"], $meta_row["parent_rel"]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем блоки с сообщениями
	protected static function _getBlockListRow(array $message_map_list, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj):array {

		$active_block_id_list = [];

		foreach ($message_map_list as $v) {

			$block_id = \CompassApp\Pack\Message\Thread::getBlockId($v);
			self::_throwIfMessageBlockNotExist($dynamic_obj, $block_id);

			$active_block_id_list[] = $block_id;
		}

		// получаем горячие блоки
		return Type_Thread_Message_Block::getActiveBlockRowList($dynamic_obj->thread_map, $active_block_id_list);
	}

	// проверяем, что блок с сообщениями существует
	protected static function _throwIfMessageBlockNotExist(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, int $block_id):void {

		if (!Type_Thread_Message_Block::isExist($dynamic_obj, $block_id)) {
			throw new ParseFatalException("this message block is not exist");
		}
	}

	// добавляем добавляем пустое сообщение в список, если есть текст у репоста/циататы
	protected static function _addEmptyMessageIfExistTextRepostOrQuote(array $message_list, array $message):array {

		$empty_message["type"] = self::_MESSAGE_TYPE_EMPTY;

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === THREAD_MESSAGE_TYPE_CONVERSATION_REPOST) {
			array_unshift($message_list, $empty_message);
		}

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === CONVERSATION_MESSAGE_TYPE_THREAD_REPOST) {
			array_unshift($message_list, $empty_message);
		}

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE) {
			array_unshift($message_list, $empty_message);
		}

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE) {
			array_unshift($message_list, $empty_message);
		}

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === THREAD_MESSAGE_TYPE_QUOTE) {
			array_unshift($message_list, $empty_message);
		}

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === THREAD_MESSAGE_TYPE_MASS_QUOTE) {
			array_unshift($message_list, $empty_message);
		}

		return $message_list;
	}

	// бросаем cs_ThreadIsLocked если у треда is_locked = 1
	protected static function _throwIfThreadIsLocked(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj):void {

		if ($dynamic_obj->is_locked == 1) {
			throw new cs_ThreadIsLocked();
		}
	}

	// бросаем cs_ThreadIsReadOnly, если у треда is_readonly = 1
	protected static function _throwIfThreadIsReadOnly(array $meta_row):void {

		if ($meta_row["is_readonly"] == 1) {
			throw new cs_ThreadIsReadOnly();
		}
	}

	// обновляем кэш пользователей
	protected static function _updateCache(array $source_parent_rel, array $cache, int $user_id, array $parent_meta_users, bool $is_user_found):void {

		$cache["parent_meta_users"] = $parent_meta_users;

		// добавляем пользователя в нужный кэш в зависимости от того был он найден или нет
		if ($is_user_found) {
			$cache["user_list"][] = $user_id;
		} else {
			$cache["not_found_user_list"][] = $user_id;
		}

		$parent_meta_map = Type_Thread_SourceParentRel::getMap($source_parent_rel);
		Type_Thread_Rel_Meta::setCache($parent_meta_map, $cache);
	}

	// проверяем что у пользователя есть доступ к родительской сущности
	protected static function _checkUserHaveAccessToParentEntity(array $meta_row, int $user_id, bool $is_need_unfollow_if_no_access):bool {

		[$source_parent_rel_dynamic] = Type_Thread_SourceParentDynamic::get($meta_row["source_parent_rel"]);
		$access_data = self::getAccessDataIfParentConversation($meta_row, $source_parent_rel_dynamic, $user_id);

		// если доступ есть
		if ($access_data["is_user_have_access"]) {
			return $access_data["is_set_readonly_false"];
		}

		// + отписываем пользователя от треда если нужно
		if ($is_need_unfollow_if_no_access) {
			Domain_Thread_Action_Follower_Unfollow::do($user_id, $meta_row["thread_map"], true);
		}

		throw new cs_Message_HaveNotAccess();
	}

	// получаем данные доступа пользователя если meta сущность - диалог
	public static function getAccessDataIfParentConversation(array $meta_row, Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic, int $user_id):array {

		$is_new_view_photo         = Type_System_Legacy::isNewViewPhoto();
		$is_parent_message_deleted = Type_Thread_ParentRel::getIsDeleted($meta_row["parent_rel"]);

		if ($is_parent_message_deleted && $is_new_view_photo) {
			throw new cs_Message_IsDeleted();
		}

		// если сообщение диалога, к которому привязан тред написано раньше чем был очищен диалог
		$parent_entity_created_at = Type_Thread_ParentRel::getCreatedAt($meta_row["parent_rel"]);
		$user_clear_until         = Type_Thread_SourceParentDynamic::getClearInfoUntil($source_parent_rel_dynamic, $user_id);

		// получаем флаг имеет ли пользователь к сообщению-родителю треда только если сообщение не должно быть очищено
		// и пользователь не скрыл сообщение
		$parent_thread_is_hidden = Type_Thread_ParentRel::isMessageHiddenByUserId($meta_row["parent_rel"], $user_id);
		$is_user_have_access     = $parent_entity_created_at >= $user_clear_until && !$parent_thread_is_hidden;

		$is_set_readonly_false = false;
		if (!$is_parent_message_deleted && $meta_row["is_readonly"]) {
			$is_set_readonly_false = true;
		}
		return [
			"is_user_have_access"   => $is_user_have_access,
			"is_set_readonly_false" => $is_set_readonly_false,
		];
	}

	// формируем список пользователей и отпрпвляем им ws событие
	protected static function _sendEventAboutDeletedMessageList(string $thread_map, array $message_map_list, array $users):void {

		$talking_user_list = Type_Thread_Meta_Users::getTalkingUserList($users);

		// отправляем событие удаления сообщения всем участникам треда
		Gateway_Bus_Sender::threadMessageListDeleted($talking_user_list, $message_map_list, $thread_map);
	}

	// обновляем список пользователей треда
	// @mixed из кэша может прийти $parent_meta_users == false
	protected static function _updateThreadMetaUsersIfNeed(string $thread_map, array $meta_row, array $parent_meta_users, bool $is_set_readonly_false):array {

		// мержим пользователей
		$users = Type_Thread_Meta_Users::doMergeUsers($meta_row["users"], $parent_meta_users);

		// если после merge пользователи изменились - обновляем мету треда
		if ($users != $meta_row["users"] || $is_set_readonly_false) {

			$set = [
				"users"      => $users,
				"updated_at" => time(),
			];
			if ($is_set_readonly_false) {

				$set["is_readonly"]      = 0;
				$meta_row["is_readonly"] = 0;
			}
			$meta_row["users"]      = $users;
			$meta_row["updated_at"] = $set["updated_at"];
			Type_Thread_Meta::set($thread_map, $set);
		}

		return $meta_row;
	}

	// формируем ответ подготовленных сообщений
	#[ArrayShape(["message" => "array", "type" => "int", "parent_message" => "array"])]
	protected static function _makeOutputPreparedMessageList(array $message, int $type_message, array $parent_message):array {

		return [
			"message"        => $message,
			"type"           => $type_message,
			"parent_message" => $parent_message,
		];
	}
}
