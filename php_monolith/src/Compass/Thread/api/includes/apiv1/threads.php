<?php

namespace Compass\Thread;

use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\SocketException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use BaseFrame\Exception\Request\BlockException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * контроллер для взаимодействия с тредами и сообщениями в них
 */
class Apiv1_Threads extends \BaseFrame\Controller\Api {

	protected const _GET_LAST_MESSAGES_BLOCK_COUNT      = 5;   // максимальное количество блоков в getLastMessages
	protected const _MAX_REPOST_MESSAGES_COUNT_LEGACY   = 15;  // маскимальное количество сообщений в репосте
	protected const _MAX_SELECTED_MESSAGES_COUNT        = 100;
	protected const _MAX_SELECTED_MESSAGES_COUNT_LEGACY = 150;
	protected const _MAX_USER_ID_LIST_FOR_EXACTINGNESS  = 30; // максимальное количество пользователей для Требовательности
	protected const _MAX_REACTION_COUNT                 = 20; // максимальное количество реакций в запросе

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"add",
		"getMenu",
		"getMenuItem",
		"getMeta",
		"doRead",
		"addMessage",
		"tryEditMessage",
		"tryDeleteMessageList",
		"tryHideMessageList",
		"addMessageReaction",
		"tryRemoveMessageReaction",
		"addQuote",
		"doReportMessage",
		"getMessages",
		"doUnfollow",
		"doFollow",
		"addRepostToConversation",
		"getMyReactions",
		"getReactionUsers",
		"getReactionsUsersBatching",
		"getMenuItemBatching",
		"getMetaBatching",
		"getTotalUnreadCount",
		"doInit",
		"setAsUnread",
		"doCommitWorkedHours",
		"tryExacting",
		"getUnreadMenu",
		"getMetaAndMenuBatching",
		"addToFavorite",
		"removeFromFavorite",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"add",
		"addMessage",
		"tryEditMessage",
		"tryDeleteMessageList",
		"tryHideMessageList",
		"addMessageReaction",
		"tryRemoveMessageReaction",
		"addQuote",
		"doReportMessage",
		"doUnfollow",
		"doFollow",
		"addRepostToConversation",
		"doInit",
		"setAsUnread",
		"doCommitWorkedHours",
		"tryExacting",
		"addToFavorite",
		"removeFromFavorite",
	];

	// методы, для вызовов которых нужно оплаченное пространство
	public const ALLOWED_WITH_PAYMENT_ONLY_METHODS = [
		"getMenu",
		"addMessage",
		"getMessages",
		"addMessageReaction",
		"tryRemoveMessageReaction",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [
		Member::ROLE_GUEST => [
			"doCommitWorkedHours",
			"tryExacting",
		],
	];

	/**
	 * Метод для создания треда
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_DecryptHasFailed
	 * @throws cs_PlatformNotFound
	 * @throws cs_Thread_ParentEntityNotFound
	 * @long
	 */
	public function add():array {

		$parent_entity_type  = $this->post(\Formatter::TYPE_INT, "type");
		$is_quote            = $this->post(\Formatter::TYPE_INT, "is_quote", 0) === 1;
		$client_message_list = $this->_getClientMessageList();
		$parent_entity_id    = $this->_getParentEntityId($parent_entity_type);

		try {

			Domain_Member_Entity_Permission::checkVoice($this->user_id, $this->method_version, Permission::IS_VOICE_MESSAGE_ENABLED, $client_message_list);
			[$message_list, $thread_meta] = Domain_Thread_Scenario_Api::add($client_message_list, $parent_entity_id, $parent_entity_type, $this->user_id, $is_quote);
		} catch (cs_ThreadIsReadOnly) {
			return $this->error(533, "thread is read only");
		} catch (cs_Message_IsTooLong) {
			return $this->error(540, "Message is too long");
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Message is deleted");
		} catch (cs_MessageList_IsEmpty) {
			return $this->error(551, "message list for quote is empty");
		} catch (cs_Message_Limit) {
			return $this->error(552, "exceeded the limit on the number of selected messages for quote");
		} catch (cs_ParentMessage_IsDeleted) {
			return $this->error(553, "the parent message was deleted");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			return $this->_returnErrorOnOpponentIsBlockedOrDisabled($e->getAllowStatus());
		} catch (cs_Message_HaveNotAccess) {
			throw new ParamException("User cant get parent message");
		} catch (cs_ParentMessage_IsRespect) {
			throw new ParamException("Not allow quote respect message to thread");
		} catch (cs_ConversationIsLocked) {
			throw new BlockException("Conversation is locked");
		} catch (cs_ThreadIsLocked) {
			throw new BlockException(__METHOD__ . " trying to write message in thread which is locked");
		} catch (cs_HiringRequestIsNotAllowedForAddThread $e) {
			throw new ParamException($e->getMessage());
		} catch (cs_Message_DuplicateClientMessageId) {

			if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
				return $this->error(541, "duplicate client_message_id");
			}
			throw new ParamException("client_message_id is duplicated");
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParamException("incorrect param client_message_list");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		return $this->ok([
			"message_list" => (array) $message_list,
			"thread_meta"  => (object) $thread_meta,
		]);
	}

	// получаем параметр client_message_list
	protected function _getClientMessageList():array {

		$client_message_list = $this->post(\Formatter::TYPE_ARRAY, "client_message_list", []);

		// проверяем параметр client_message_list на корректность
		if (!Type_Api_Validator::isCorrectClientMessageList($client_message_list)) {
			throw new ParamException("incorrect param client_message_list");
		}

		return $client_message_list;
	}

	/**
	 * получаем параметр id родительского сущности треда
	 *
	 * @throws cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	protected function _getParentEntityId(int $parent_entity_type):mixed {

		switch ($parent_entity_type) {

			// тред к сообщению диалога
			case PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE:

				$parent_id   = $this->post(\Formatter::TYPE_STRING, "parent_id", false);
				$message_key = $this->post(\Formatter::TYPE_STRING, "message_key", false);

				// если передан parent_id, то берем его
				if ($parent_id !== false) {
					$message_key = $parent_id;
				}

				try {
					$message_map = \CompassApp\Pack\Message::doDecrypt($message_key);
				} catch (\cs_DecryptHasFailed) {
					throw new ParamException("passed wrong message key");
				}

				if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
					throw new ParamException(__METHOD__ . " is not conversation message");
				}

				return $message_map;

			case PARENT_ENTITY_TYPE_HIRING_REQUEST:
			case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:

				return $this->post(\Formatter::TYPE_INT, "parent_id");

			default:
				throw new ParamException(__METHOD__ . " Type not found");
		}
	}

	/**
	 * метод для получения всех ответов
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \paramException
	 */
	public function getMenu():array {

		$offset          = $this->post(\Formatter::TYPE_INT, "offset", 0);
		$count           = $this->post(\Formatter::TYPE_INT, "count", Domain_Thread_Entity_Validator::MAX_THREAD_MENU_COUNT);
		$filter_favorite = $this->post(\Formatter::TYPE_INT, "filter_favorite", 0);
		$filter_unread   = $this->post(\Formatter::TYPE_INT, "filter_unread", 0);

		// получаем информацию о тредах
		if (Type_System_Legacy::isGetMenuV2()) {

			[$thread_menu, $thread_list_data, $favorite_count, $has_next] = Domain_Thread_Scenario_Api::getMenuV2($this->user_id, $count, $offset, $filter_favorite, $filter_unread);
		} else {

			Gateway_Bus_CollectorAgent::init()->inc("row0");
			[$thread_menu, $thread_list_data, $favorite_count, $has_next] = Domain_Thread_Scenario_Api::getMenu($offset, $count, $this->user_id);
		}

		return $this->ok([
			"thread_menu"      => (array) $thread_menu,
			"thread_list_data" => (array) $thread_list_data,
			"has_next"         => (int) $has_next,
			"favorite_count"   => (int) $favorite_count,
		]);
	}

	/**
	 * метод для получения непрочитанных ответов
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getUnreadMenu():array {

		$offset = $this->post(\Formatter::TYPE_INT, "offset", 0);
		$count  = $this->post(\Formatter::TYPE_INT, "count", Domain_Thread_Entity_Validator::MAX_THREAD_MENU_COUNT);

		// получаем информацию о непрочитанных тредах
		[$thread_menu, $thread_list_data, $has_next] = Domain_Thread_Scenario_Api::getUnreadMenu($offset, $count, $this->user_id);

		return $this->ok([
			"thread_menu"      => (array) $thread_menu,
			"thread_list_data" => (array) $thread_list_data,
			"has_next"         => (int) $has_next,
		]);
	}

	/**
	 * метод для получения меты треда и его родительской сущности
	 *
	 * @throws \parseException|\paramException|cs_DecryptHasFailed|\returnException
	 */
	public function getMenuItem():array {

		$thread_key = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);

		// получаем мету треда, если пользователь является участником треда
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|cs_Message_IsDeleted) {

			Domain_Thread_Action_Follower_Unfollow::do($this->user_id, $thread_map, true);
			return $this->_returnError530();
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {

			// в таком случае имеем право обращаться с тредом только на чтение
			$meta_row = $e->getMetaRow();
		}

		// получаем информацию о родительской сущности треда
		try {
			$parent_entity = Type_Thread_Rel_Parent::getEntityData($meta_row["parent_rel"], $this->user_id);
		} catch (cs_Message_HaveNotAccess) {

			Domain_Thread_Action_Follower_Unfollow::do($this->user_id, $thread_map, true);
			return $this->_returnError530();
		} catch (cs_Thread_ParentEntityNotFound) {
			return $this->_returnError531();
		}

		// подготавливаем thread_meta и инкрементим статистику
		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($meta_row, $this->user_id);
		$this->_incSuccessStatisticForGetMenuItem($parent_entity["parent_type"]);

		// добавляем пользователей к ответу
		$this->action->users(Type_Thread_Meta::getActionUsersList($meta_row));
		$this->action->users($parent_entity["users"]);

		$output = Apiv1_Format::getMenuItem($prepared_thread_meta, $parent_entity, true);
		return $this->ok($output);
	}

	// метод для того что бы вывести 531 ошибку
	protected function _returnError531():array {

		return $this->error(531, "parent entity not found");
	}

	// инкрементим кол-во успешных вызовов метода
	protected function _incSuccessStatisticForGetMenuItem(int $parent_type):void {

		// собираем статистику в зависимости от типа родительской сущности
		switch ($parent_type) {

			case PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE:
			case PARENT_ENTITY_TYPE_THREAD_MESSAGE:
			case PARENT_ENTITY_TYPE_HIRING_REQUEST:
			case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:
				return;

			default:
				throw new ParseFatalException("unhandled thread parent type in method " . __METHOD__);
		}
	}

	/**
	 * метод для получения меты тредов и их родительской сущности
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getMenuItemBatching():array {

		$thread_key_list = $this->post(\Formatter::TYPE_ARRAY, "thread_key_list");
		$signature       = $this->post(\Formatter::TYPE_STRING, "signature");

		$this->_throwIfThreadListIsIncorrect($thread_key_list);
		$this->_throwIfPassedIncorrectSignature($thread_key_list, $signature);
		$thread_map_list = $this->_tryDecryptThreadList($thread_key_list);

		// получаем список мет тредов и те треды, к которым у пользователя нет доступа
		$thread_data                 = $this->_getMetaListIfUserMember($thread_map_list);
		$meta_list                   = $thread_data["meta_list"];
		$not_allowed_thread_map_list = $thread_data["not_access_thread_map_list"];

		// формируем список с информацией о родительских сущностях тредов
		$thread_parent_rel_list = [];
		foreach ($meta_list as $meta_row) {
			$thread_parent_rel_list[$meta_row["thread_map"]] = $meta_row["parent_rel"];
		}

		// пробуем получить родительские сущности тредов
		$parent_entity_data = $this->_tryGetParentEntityList($thread_parent_rel_list);
		$parent_entity_list = $parent_entity_data["parent_entity_list"];

		// добавляем map тех тредов, чьи родители оказались недоступны для пользователя
		$not_allowed_thread_map_list = $this->_addThreadMapListIfExistNotAccessParent($not_allowed_thread_map_list, $parent_entity_data);

		// отправляем задачу на отписку от тех тредов, к родителям которых у пользователя нет доступа
		Type_Phphooker_Main::doUnfollowThreadList($not_allowed_thread_map_list, $this->user_id);

		// собираем ответ
		$thread_list = $this->_getPreparedThreadList($meta_list, $not_allowed_thread_map_list, $parent_entity_list, $thread_map_list);
		return $this->_returnOkGetMenuItemBatching($thread_list, $not_allowed_thread_map_list);
	}

	// выбрасываем ошибку, если пришла некорректная подпись
	protected function _throwIfPassedIncorrectSignature(array $thread_key_list, string $signature):void {

		// если пришла некорректная подпись
		if (!Type_Thread_Utils::verifySignatureWithCustomSalt($thread_key_list, $signature, SALT_THREAD_LIST)) {
			throw new ParamException(__METHOD__ . " wrong signature");
		}
	}

	// получение списка мет тредов
	#[ArrayShape(["meta_list" => "array|mixed", "not_access_thread_map_list" => "array|mixed"])]
	protected function _getMetaListIfUserMember(array $thread_map_list):array {

		$data = Helper_Threads::getMetaListIfUserMember($thread_map_list, $this->user_id);

		$meta_list                  = $data["allowed_meta_list"];
		$not_access_thread_map_list = $data["not_allowed_thread_map_list"];

		return [
			"meta_list"                  => $meta_list,
			"not_access_thread_map_list" => $not_access_thread_map_list,
		];
	}

	// получаем список с родительской сущностью тредов
	protected function _tryGetParentEntityList(array $thread_parent_rel_list):array {

		$output = ["parent_entity_list" => [], "not_access_thread_map_list" => []];

		// пробуем получить информацию о родителях из кэша
		$parent_entity_list        = [];
		$not_found_parent_rel_list = [];
		foreach ($thread_parent_rel_list as $thread_map => $parent_rel) {

			switch (Type_Thread_ParentRel::getType($parent_rel)) {

				case PARENT_ENTITY_TYPE_HIRING_REQUEST:
				case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:
					$not_found_parent_rel_list[$thread_map] = $parent_rel;
					break;

				default:
					$parent_message_map = Type_Thread_ParentRel::getMap($parent_rel);
					$cache              = Type_Thread_Rel_Cache::get($parent_message_map);

					// если не нашли, то добавляем в список тех, для кого отправим сокет-запрос в php_conversation
					if (!$cache) {

						$not_found_parent_rel_list[$thread_map] = $parent_rel;
						break;
					}

					$parent_entity_list[$thread_map] = $cache["parent_entity"];
			}
		}

		// возвращаем список, если вся информация о родителях нашлась в кэше
		if (count($not_found_parent_rel_list) == 0) {

			$output["parent_entity_list"] = $parent_entity_list;
			return $output;
		}

		// делаем запрос за ненайденными в кэше родителями, и возвращаем весь полный список родительских сущностей
		return $this->_returnAllParentEntityList($parent_entity_list, $not_found_parent_rel_list, $output);
	}

	// возвращаем весь список родительских сущностей тредов
	protected function _returnAllParentEntityList(array $parent_entity_list, array $not_found_parent_rel_list, array $output):array {

		// получаем список информации о родительских сообщениях тредов
		// и список map тех тредов, родители которых стали недоступны для юзера
		[$conversation_message_entity_list, $request_entity_list, $not_access_thread_map_list] = Type_Thread_Rel_Parent::getEntityDataList(
			$not_found_parent_rel_list, $this->user_id
		);

		// добавляем в кэш каждую полученную информацию о родителях треда
		$this->_addParentEntityListToCache($conversation_message_entity_list);

		// добавляем полученную информацию о родителях в список сущностей, сортировав по thread_map
		foreach ($not_found_parent_rel_list as $thread_map => $parent_rel) {

			$parent_id   = Type_Thread_ParentRel::getMap($parent_rel);
			$parent_type = Type_Thread_ParentRel::getType($parent_rel);

			// здесь для каждого треда раскидываем по родителю
			switch ($parent_type) {

				case PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE:

					if (isset($conversation_message_entity_list[$parent_id])) {
						$parent_entity_list[$thread_map] = $conversation_message_entity_list[$parent_id];
					}
					break;

				case PARENT_ENTITY_TYPE_HIRING_REQUEST:
				case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:

					if (isset($request_entity_list[$parent_type][$parent_id])) {
						$parent_entity_list[$thread_map] = $request_entity_list[$parent_type][$parent_id];
					}
			}
		}

		$output["parent_entity_list"]         = $parent_entity_list;
		$output["not_access_thread_map_list"] = $not_access_thread_map_list;

		return $output;
	}

	// добавляем полученных родителей в кэш
	protected function _addParentEntityListToCache(array $parent_entity_list):void {

		foreach ($parent_entity_list as $parent_message_map => $v) {

			$cache["parent_entity"] = $v;
			Type_Thread_Rel_Cache::set($parent_message_map, $cache);
		}
	}

	// добавляем map тех тредов, чьи родители оказались недоступны для пользователя
	protected function _addThreadMapListIfExistNotAccessParent(array $not_allowed_thread_map_list, array $parent_entity_data):array {

		if (!isset($parent_entity_data["not_access_thread_map_list"]) || count($parent_entity_data["not_access_thread_map_list"]) < 1) {
			return $not_allowed_thread_map_list;
		}

		return array_merge($not_allowed_thread_map_list, $parent_entity_data["not_access_thread_map_list"]);
	}

	// получить подготовленный для ответа thread_list
	protected function _getPreparedThreadList(array $meta_list, array $not_allowed_thread_map_list, array $parent_entity_list, array $thread_map_list):array {

		$thread_list = [];
		foreach ($meta_list as $v) {

			$thread_map = $v["thread_map"];

			if (in_array($thread_map, $not_allowed_thread_map_list)) {
				continue;
			}

			// приводим мету в формат и добавляем в список мет в том порядке, в котором получили thread_map_list
			$index               = array_search($thread_map, $thread_map_list);
			$thread_list[$index] = $this->_makeGetMenuItem($v, $parent_entity_list[$thread_map]);
		}

		// возвращаем в том же порядке, в котором получили thread_map_list
		ksort($thread_list);

		return array_values($thread_list);
	}

	// формируем ответ метода getMenuItemBatching
	protected function _makeGetMenuItem(array $meta_row, array $parent_entity):array {

		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($meta_row, $this->user_id);

		$this->action->users(Type_Thread_Meta::getActionUsersList($meta_row));
		$this->action->users($parent_entity["users"]);
		$this->action->users($prepared_thread_meta["sender_user_list"]);

		$menu_item = Apiv1_Format::getMenuItem($prepared_thread_meta, $parent_entity);

		// докидываем id нанимаемого пользователя если есть
		if ($menu_item["parent_type"] == "hiring_request" && $menu_item["parent_data"]->request->candidate_user_id != 0) {
			$this->action->users([$menu_item["parent_data"]->request->candidate_user_id]);
		}

		return $menu_item;
	}

	// возвращаем ok в методе getMenuItemBatching
	protected function _returnOkGetMenuItemBatching(array $thread_list, array $not_allowed_thread_map_list):array {

		// если массив map недоступных тредов не пустой
		$not_allowed_thread_key_list = [];
		if (count($not_allowed_thread_map_list) > 0) {

			foreach ($not_allowed_thread_map_list as $item) {
				$not_allowed_thread_key_list[] = \CompassApp\Pack\Thread::doEncrypt($item);
			}
		}

		return $this->ok([
			"list"                        => (array) $thread_list,
			"not_allowed_thread_key_list" => (array) $not_allowed_thread_key_list,
		]);
	}

	/**
	 * метод для получения thread_meta
	 *
	 * @throws cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException|\returnException
	 */
	public function getMeta():array {

		$thread_key = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);

		// получаем мету треда, если пользователь является участником треда
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|cs_Message_IsDeleted) {

			Domain_Thread_Action_Follower_Unfollow::do($this->user_id, $thread_map, true);
			return $this->_returnError530();
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {

			// в таком случае имеем право обращаться с тредом только на чтение
			$meta_row = $e->getMetaRow();
		}

		// подготавливаем thread_meta для форматирования под фронтенд
		$prepared_meta_row = Type_Thread_Utils::prepareThreadMetaForFormat($meta_row, $this->user_id);

		// добавляем пользователей к ответу
		$this->action->users(Type_Thread_Meta::getActionUsersList($meta_row));

		return $this->ok([
			"thread_meta" => (object) Apiv1_Format::threadMeta($prepared_meta_row),
		]);
	}

	/**
	 * метод для получения thread_meta запрошенных тредов
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getMetaBatching():array {

		$thread_key_list = $this->post(\Formatter::TYPE_ARRAY, "thread_key_list");

		// бросаем ошибку, если пришел некорректный массив тредов
		$this->_throwIfThreadListIsIncorrect($thread_key_list);

		// преобразуем все key в map
		$thread_map_list = $this->_tryDecryptThreadList($thread_key_list);

		// пробуем получить данные о метах тредов
		$thread_data                 = $this->_getMetaListIfUserMember($thread_map_list);
		$meta_list                   = $thread_data["meta_list"];
		$not_allowed_thread_map_list = $thread_data["not_access_thread_map_list"];

		// отправляем задачу на отписывание от тредов
		Type_Phphooker_Main::doUnfollowThreadList($not_allowed_thread_map_list, $this->user_id);

		// формируем ответ
		$output = $this->_makeGetMetaBatchingOutput($meta_list);

		return $this->ok([
			"thread_meta_list" => (array) $output,
		]);
	}

	// формируем ответ для метода threads.getMetaBatching
	protected function _makeGetMetaBatchingOutput(array $thread_meta_list):array {

		$output = [];
		foreach ($thread_meta_list as $item) {

			// приводим сущность threads под формат frontend
			$prepared_meta_row = Type_Thread_Utils::prepareThreadMetaForFormat($item, $this->user_id);
			$output[]          = Apiv1_Format::threadMeta($prepared_meta_row);

			// добавляем пользователей в actions users
			$this->action->users(Type_Thread_Meta::getActionUsersList($item));
		}

		return $output;
	}

	/**
	 * пометить тред прочитанным (обнулить unread_count)
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doRead():array {

		$message_key = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map = \CompassApp\Pack\Message::tryDecrypt($message_key);

		// сообщение из треда?
		$this->_throwIfMessageMapIsNotFromThread($message_map);

		[$local_date, $local_time, $_] = getLocalClientTime();
		Domain_Thread_Scenario_Api::doRead($this->user_id, $message_map, $local_date, $local_time);

		return $this->ok();
	}

	/**
	 * отправить сообщение в тред
	 *
	 * @throws cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addMessage():array {

		// если легаси вызов, что вызываем старый метод
		if (!Type_System_Legacy::isLongMessageSupported()) {
			return $this->_addMessageLegacy();
		}

		$thread_key          = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$thread_map          = \CompassApp\Pack\Thread::tryDecrypt($thread_key);
		$client_message_list = $this->post(\Formatter::TYPE_ARRAY, "client_message_list");

		return $this->_processAddMessage($thread_map, $client_message_list);
	}

	// отправление одного сообщения
	protected function _addMessageLegacy():array {

		$thread_key = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);

		$client_message_id = $this->post(\Formatter::TYPE_STRING, "client_message_id");
		$text              = $this->post(\Formatter::TYPE_STRING, "text");
		$file_key          = $this->post(\Formatter::TYPE_STRING, "file_key", false);
		$file_name         = $this->post(\Formatter::TYPE_STRING, "file_name", "");

		// оборачиваем, чтобы код не дублировать
		$client_message = [
			"client_message_id" => $client_message_id,
			"text"              => $text,
			"file_name"         => $file_name,
			"order"             => 0,
		];
		if ($file_key !== false) {
			$client_message["file_key"] = $file_key;
		}

		return $this->_processAddMessage($thread_map, [$client_message]);
	}

	// обертка для логики вызывается из создания сообщений
	// @long обертка для паблик метода
	protected function _processAddMessage(string $thread_map, array $client_message_list):array {

		// проверяем антиспам
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_ADDMESSAGE, "threads", "row102");

		try {

			// юзер является участником существующего треда
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess) {
			return $this->error(530, "You are not allow to do this action");
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Message is deleted");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			return $this->_returnErrorOnOpponentIsBlockedOrDisabled($e->getAllowStatus());
		}

		// готовим к работе список сообщений
		try {

			$client_message_list = Type_Thread_Utils::parseRawMessageList($client_message_list);
			Domain_Member_Entity_Permission::checkVoice($this->user_id, $this->method_version, Permission::IS_VOICE_MESSAGE_ENABLED, $client_message_list);
		} catch (cs_Message_IsTooLong) {
			return $this->error(540, "Message is too long");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		return $this->_addMessageList($thread_map, $meta_row, $client_message_list);
	}

	/**
	 * метод изменения сообщения
	 *
	 * @throws \blockException
	 * @throws cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function tryEditMessage():array {

		$message_key   = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map   = \CompassApp\Pack\Message::tryDecrypt($message_key);
		$text          = $this->post(\Formatter::TYPE_STRING, "text");
		$is_new_errors = Type_System_Legacy::isNewErrors();

		$this->_throwIfMessageMapIsNotFromThread($message_map);
		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

		// конвертируем emoji и проверяем что длина сообщения не превышает допустимую
		$text = Type_Api_Filter::replaceEmojiWithShortName($text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			return $this->error(540, "Message is too long");
		}

		// фильтруем текст и инкрементим блокировку
		$text = Type_Api_Filter::sanitizeMessageText($text);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_TRYEDITMESSAGE, "threads", "row123");

		// пытаемся получить мету треда, проверяем права доступа
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->_returnError530();
		} catch (cs_Thread_UserNotMember) {
			return $this->_returnErrorIfUserNotMemberOfThread();
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {

			if ($is_new_errors) {
				return $this->_returnErrorOnOpponentIsBlockedOrDisabled($e->getAllowStatus());
			}
			$meta_row = $e->getMetaRow();
		}
		$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($meta_row, $text);
		$follower_row         = Helper_Threads::attachUsersToThread($meta_row, $mention_user_id_list);

		return $this->_editMessage($thread_map, $meta_row, $message_map, $text, $mention_user_id_list, $follower_row, $is_new_errors);
	}

	// редактируем сообщение
	// @long - try с кучей исключений
	protected function _editMessage(string $thread_map, array $meta_row, string $message_map, string $text, array $mention_user_id_list, array $follower_row, bool $is_new_errors):array {

		try {

			$prepared_message = Helper_Threads::editMessageText(
				$thread_map, $meta_row, $message_map, $this->user_id, $text, $mention_user_id_list, $follower_row
			);
		} catch (cs_Message_IsEmptyText) {
			throw new ParamException("Empty text");
		} catch (cs_ThreadIsLocked) {
			throw new BlockException("thread is locked");
		} catch (cs_ThreadIsReadOnly) {
			return $this->error(533, "thread is read only");
		} catch (cs_Message_UserNotSender) {

			if ($is_new_errors) {
				return $this->error(514, "You are not have permission to this action");
			}
			throw new ParamException("User not the sender of the message");
		} catch (cs_Message_IsNotAllowForEdit) {
			throw new ParamException("User have not permissions to edit this message");
		} catch (cs_Message_IsTimeNotAllowForDoAction) {
			return $this->error(917, "Time is over for edit message");
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Message is deleted");
		}

		return $this->ok([
			"message" => (object) Apiv1_Format::threadMessage($prepared_message),
		]);
	}

	/**
	 * метод удаления сообщений
	 *
	 * @throws \blockException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function tryDeleteMessageList():array {

		$message_key_list                = $this->post(\Formatter::TYPE_JSON, "message_key_list", []);
		$message_map_list                = $this->_tryGetMessageMapList($message_key_list);
		$is_new_try_delete_message_error = Type_System_Legacy::isNewTryDeleteMessageError();

		// инкрементим блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_TRYDELETEMESSAGE, "threads", "row142");
		$this->_throwIfMessageMapListIsInvalid($message_map_list);
		if (count($message_map_list) > self::_MAX_SELECTED_MESSAGES_COUNT) {
			throw new ParamException("Overflow the limit of selected messages");
		}

		// получаем thread_map
		$thread_map = $this->_getThreadMapFromMessageMapList($message_map_list);

		// пробуем получить мета треда
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id, false);
		} catch (cs_Message_HaveNotAccess) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Thread_UserNotMember) {
			return $this->_returnErrorIfUserNotMemberOfThread();
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {

			if ($is_new_try_delete_message_error) {
				return $this->_returnErrorOnOpponentIsBlockedOrDisabled($e->getAllowStatus());
			}
			$meta_row = $e->getMetaRow();
		}

		// удаляем сообщения
		return $this->_deleteMessageList($message_map_list, $thread_map, $this->user_id, $meta_row, $is_new_try_delete_message_error);
	}

	// удаляем сообщения из треда
	protected function _deleteMessageList(array $message_map_list, string $thread_map, int $user_id, array $meta_row, bool $is_new_try_delete_message_error):array {

		$is_forced = Permission::canDeleteMessage($this->role, $this->permissions);

		try {

			Helper_Threads::deleteMessageList(
				$user_id, $thread_map, $message_map_list, $meta_row, $is_new_try_delete_message_error, $is_forced
			);
		} catch (cs_ThreadIsReadOnly) {
			return $this->error(533, "thread is read only");
		} catch (cs_Message_UserNotSender|cs_Message_IsNotAllowForDelete $e) {

			if ($is_new_try_delete_message_error && $e instanceof cs_Message_UserNotSender) {
				return $this->error(514, "You are not have permission to this action");
			}
			$this->_throwIfUserNotSenderMessageOrNotAllowForDeleteMessage($e);
		} catch (cs_Message_IsTimeNotAllowToDelete) {
			return $this->error(917, "Message deletion timed out");
		}

		return $this->ok();
	}

	/**
	 * метод для скрытия сообщения
	 *
	 * @throws \blockException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function tryHideMessageList():array {

		$message_key_list = $this->post(\Formatter::TYPE_JSON, "message_key_list");
		$message_map_list = $this->_tryGetMessageMapList($message_key_list);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_TRYHIDEMESSAGE, "threads", "row662");
		$this->_throwIfMessageMapListIsInvalid($message_map_list);
		$this->_throwIfMessageMapListOverflow($message_map_list);

		// получаем thread_map и проверяем его валидность
		$thread_map = $this->_getThreadMapFromMessageMapList($message_map_list);

		// проверяем что пользователь имеет доступ к треду
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Message_HaveNotAccess) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Thread_UserNotMember) {
			return $this->_returnErrorIfUserNotMemberOfThread();
		} catch (cs_Conversation_IsBlockedOrDisabled) {
			$meta_row = Type_Thread_Meta::getOne($thread_map);
		}

		// скрываем сообщения
		Helper_Threads::hideMessageList($thread_map, $meta_row, $message_map_list, $this->user_id);

		return $this->ok();
	}

	/**
	 * поставить реакцию на сообщение
	 *
	 * @throws \blockException
	 * @throws cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function addMessageReaction():array {

		$message_key   = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map   = \CompassApp\Pack\Message::tryDecrypt($message_key);
		$reaction_name = $this->post(\Formatter::TYPE_STRING, "reaction_name");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_SETREACTION);

		try {
			Domain_Thread_Scenario_Api::addReaction($message_map, $reaction_name, $this->user_id);
		} catch (cs_Thread_UserNotMember) {
			return $this->_returnErrorIfUserNotMemberOfThread();
		} catch (cs_Message_HaveNotAccess) {
			return $this->error(530, "User cant get parent message");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			return $this->_returnErrorOnOpponentIsBlockedOrDisabled($e->getAllowStatus());
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "message is deleted");
		} catch (cs_Message_IsNotAllowedForReaction) {
			throw new ParamException("Trying to set reaction on message, which not allow to do this action");
		} catch (cs_Message_ReactionLimit) {
			return $this->error(545, "message has max count reactions");
		} catch (cs_ThreadIsReadOnly) {
			return $this->error(533, "thread is read only");
		}

		return $this->ok();
	}

	/**
	 * убрать реакцию сообщения
	 *
	 * @throws \blockException
	 * @throws cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function tryRemoveMessageReaction():array {

		$message_key   = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map   = \CompassApp\Pack\Message::tryDecrypt($message_key);
		$reaction_name = $this->post(\Formatter::TYPE_STRING, "reaction_name");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_SETREACTION);

		try {
			Domain_Thread_Scenario_Api::removeReaction($message_map, $reaction_name, $this->user_id);
		} catch (cs_Thread_UserNotMember) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Message_HaveNotAccess) {
			return $this->error(530, "User cant get parent message");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			return $this->_returnErrorOnOpponentIsBlockedOrDisabled($e->getAllowStatus());
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Message is deleted");
		} catch (cs_Message_IsNotAllowedForReaction) {
			throw new ParamException("Trying to remove reaction on message, which not allow to do this action");
		} catch (cs_ThreadIsReadOnly) {
			return $this->error(533, "thread is read only");
		}
		return $this->ok();
	}

	/**
	 * цитируем сообщение
	 *
	 * @throws \parseException|\paramException
	 */
	public function addQuote():array {

		$text              = $this->post(\Formatter::TYPE_STRING, "text", "");
		$client_message_id = $this->post(\Formatter::TYPE_STRING, "client_message_id");

		// версия репост репостов и цитат или V2
		if (Type_System_Legacy::isAddRepostQuote() || Type_System_Legacy::isAddQuoteV2()) {
			return $this->_addQuote($client_message_id, $text);
		}

		// обычное цитирование
		return $this->_addQuoteLegacy($client_message_id, $text);
	}

	// цитируем сообщения
	// @long
	protected function _addQuote(string $client_message_id, string $text):array {

		$message_key_list = $this->post(\Formatter::TYPE_JSON, "message_key_list", []);
		$message_map_list = $this->_tryGetMessageMapList($message_key_list);

		$is_attach_parent = $this->post(\Formatter::TYPE_INT, "is_attach_parent", 0);
		$thread_key       = $this->post(\Formatter::TYPE_STRING, "thread_key", "");
		$thread_map       = mb_strlen($thread_key) > 0 ? \CompassApp\Pack\Thread::tryDecrypt($thread_key) : "";

		$this->_throwIfIncorrectParamsForAddQuote($message_map_list, $is_attach_parent, $thread_map);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_ADDREPOSTTOCONVERSATION, "threads", "row223");

		$text = Type_Api_Filter::replaceEmojiWithShortName($text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			return $this->error(540, "Message is too long");
		}
		$text = Type_Api_Filter::sanitizeMessageText($text);
		$this->_throwIfIncorrectClientMessageId($client_message_id);

		// если получили ключи выбранных для цитирования сообщений
		if (count($message_map_list) > 0) {

			if (count($message_map_list) > self::_MAX_SELECTED_MESSAGES_COUNT) {
				return $this->error(552, "exceeded the limit on the number of selected messages for quote");
			}
			$thread_map = $this->_getThreadMapIfMessageMapListCorrect($message_map_list);
		}

		try {

			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember) {
			return $this->_returnErrorIfUserNotMemberOfThread();
		} catch (cs_Message_HaveNotAccess) {
			return $this->error(530, "this user does not have access to this thread");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			return $this->_returnErrorOnOpponentIsBlockedOrDisabled($e->getAllowStatus());
		}

		$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($meta_row, $text);
		Helper_Threads::attachUsersToThread($meta_row, $mention_user_id_list);

		// сортируем message_map_list по порядку в треде
		$message_map_list = self::_doSortMessageMapListByMessageIndex($message_map_list);

		if (Type_System_Legacy::isAddQuoteV2()) {
			return $this->_addQuoteMessageV2($meta_row, $message_map_list, $client_message_id, $text, $mention_user_id_list, $is_attach_parent);
		}

		return $this->_addQuoteMessage($meta_row, $message_map_list, $text, $client_message_id, $mention_user_id_list, $is_attach_parent);
	}

	// выбрасываем exception, если пришли некорректные параметры для добавления цитаты
	protected function _throwIfIncorrectParamsForAddQuote(array $message_map_list, int $is_attach_parent, string $thread_map):void {

		// если имеются ключи выбранных сообщений для цитаты, то дальше проверку не продолжаем
		if (count($message_map_list) > 0) {
			return;
		}

		// если не передан флаг получения родителя или thread_map пуст, то выдаем exception
		if ($is_attach_parent == 0 || mb_strlen($thread_map) == 0) {
			throw new ParamException("Param is_attach_parent and/or thread_map is empty");
		}
	}

	// получаем thread_map если map выбранных сообщений корректен
	protected function _getThreadMapIfMessageMapListCorrect(array $message_map_list):string {

		$this->_throwIfMessageMapDuplicated($message_map_list);
		$this->_throwIfIncorrectThread($message_map_list);

		// валидируем thread_map
		return \CompassApp\Pack\Message\Thread::getThreadMap($message_map_list[0]);
	}

	// выполняем цитирование массива сообщений
	// @long
	protected function _addQuoteMessageV2(array $meta_row, array $message_map_list, string $client_message_id, string $text, array $mention_user_id_list, int $is_attach_parent):array {

		try {

			$platform       = Type_Api_Platform::getPlatform();
			$parent_message = Type_Thread_Rel_Parent::getParentMessageIfNeed($this->user_id, $meta_row, $is_attach_parent == 1);
			$data_list      = Helper_Threads::addQuoteV2(
				$meta_row["thread_map"], $meta_row, $message_map_list, $client_message_id, $this->user_id, $text, $mention_user_id_list, $parent_message, $platform
			);
		} catch (cs_ThreadIsLocked) {
			throw new BlockException(__METHOD__ . " trying to write message in thread which is locked");
		} catch (cs_ThreadIsReadOnly) {
			return $this->error(533, "thread is read only");
		} catch (cs_MessageList_IsEmpty) {
			return $this->error(551, "message list for quote is empty");
		} catch (cs_Message_Limit) {
			return $this->error(552, "exceeded the limit on the number of selected messages for quote");
		} catch (cs_ParentMessage_IsDeleted) {
			return $this->error(553, "the parent message was deleted");
		} catch (cs_ParentMessage_IsRespect) {
			throw new ParamException("Not allow quote respect message to thread");
		} catch (cs_Message_DuplicateClientMessageId) {

			if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
				return $this->error(541, "duplicate client_message_id");
			}
			throw new ParamException("client_message_id is duplicated");
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParamException("passed empty message list");
		}

		return $this->_returnOutputForAddQuoteV2($data_list);
	}

	// возвращаем ответ для цитирования
	protected function _returnOutputForAddQuoteV2(array $data_list):array {

		// подводим под формат и отдаем
		$prepared_message_list = [];
		foreach ($data_list["message_list"] as $message) {

			$prepared_message        = Type_Thread_Message_Main::getHandler($message)::prepareForFormat($message);
			$prepared_message_list[] = Apiv1_Format::threadMessage($prepared_message);
		}

		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($data_list["meta_row"], $this->user_id);

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::THREAD_MESSAGE, $this->user_id, count($prepared_message_list));
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_THREAD_MESSAGE);

		return $this->ok([
			"message_list" => (array) $prepared_message_list,
			"thread_meta"  => (object) Apiv1_Format::threadMeta($prepared_thread_meta),
		]);
	}

	// цитируем одно сообщение
	// @long
	protected function _addQuoteLegacy(string $client_message_id, string $text):array {

		$message_key = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map = \CompassApp\Pack\Message::tryDecrypt($message_key);

		$this->_throwIfMessageMapIsNotFromThread($message_map);
		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

		$this->_throwIfIncorrectClientMessageId($client_message_id);
		$text = Type_Api_Filter::replaceEmojiWithShortName($text);

		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			return $this->error(540, "Message is too long");
		}

		$text = Type_Api_Filter::sanitizeMessageText($text);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_ADDMESSAGE, "threads", "row223");

		// получаем мету треда
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember) {
			return $this->_returnErrorIfUserNotMemberOfThread();
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->error(530, "this user does not have access to this thread");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			return $this->_returnErrorOnOpponentIsBlockedOrDisabled($e->getAllowStatus());
		}

		$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($meta_row, $text);
		Helper_Threads::attachUsersToThread($meta_row, $mention_user_id_list);

		try {
			$message = $this->_makeQuoteMessageLegacy($thread_map, $message_map, $text, $client_message_id, $mention_user_id_list);
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Message is deleted");
		}

		return $this->_doAddMessage($thread_map, $meta_row, $message, $text);
	}

	// создаем сообщение с цитатой
	protected function _makeQuoteMessageLegacy(string $thread_map, string $message_map, string $text, string $client_message_id, array $mention_user_id_list):array {

		$quoted_message = $this->_getMessage($thread_map, $message_map);

		if (Type_Thread_Message_Main::getHandler($quoted_message)::isMessageDeleted($quoted_message)) {
			throw new cs_Message_IsDeleted();
		}

		// флаги/тип сообщения позволяют его цитировать?
		if (!Type_Thread_Message_Main::getHandler($quoted_message)::isAllowToQuote($quoted_message, $this->user_id)) {
			throw new ParamException(__METHOD__ . ": you have not permissions to quote this message");
		}

		// создаем сообщение с цитатой
		$platform = Type_Api_Platform::getPlatform();
		$message  = Type_Thread_Message_Main::getLastVersionHandler()::makeQuote($this->user_id, $text, $client_message_id, $quoted_message, $platform);
		$message  = Type_Thread_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

		return $message;
	}

	// добавляем сообщение
	// @long
	protected function _doAddMessage(string $thread_map, array $meta_row, array $message, string $text):array {

		try {
			$data = Domain_Thread_Action_Message_Add::do($thread_map, $meta_row, $message);
		} catch (cs_ThreadIsLocked) {
			throw new BlockException(__METHOD__ . " trying to write message in thread which is locked");
		} catch (cs_ThreadIsReadOnly) {
			return $this->error(533, "thread is read only");
		} catch (cs_Message_DuplicateClientMessageId) {

			if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
				return $this->error(541, "duplicate client_message_id");
			}
			throw new ParamException("client_message_id is duplicated");
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParamException("passed empty message list");
		}

		$prepared_message     = Type_Thread_Message_Main::getHandler($data["message"])::prepareForFormat($data["message"]);
		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($data["meta_row"], $this->user_id);
		$this->action->users(Type_Thread_Meta::getActionUsersList($data["meta_row"]));

		return $this->ok([
			"message"     => (object) Apiv1_Format::threadMessage($prepared_message),
			"thread_meta" => (object) Apiv1_Format::threadMeta($prepared_thread_meta),
		]);
	}

	/**
	 * пожаловаться на сообщение с неприемлимым контентом
	 *
	 * @throws \blockException
	 * @throws \paramException
	 * @throws \parseException|cs_DecryptHasFailed|\returnException
	 */
	public function doReportMessage():array {

		$message_key = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map = \CompassApp\Pack\Message::tryDecrypt($message_key);
		$reason      = $this->post(\Formatter::TYPE_STRING, "reason", "");

		$this->_throwIfMessageMapIsNotFromThread($message_map);
		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

		// фильтруем и валидируем reason
		$reason = Type_Api_Filter::sanitizeReason($reason);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_DOREPORTMESSAGE, "threads", "row243");

		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->error(530, "this user does not have access to this thread");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$meta_row = $e->getMetaRow();
		}

		// репортим сообщение и скрываем его для пользователя
		return $this->_reportMessageFromThread($meta_row, $message_map, $reason);
	}

	// репортим сообщение
	protected function _reportMessageFromThread(array $meta_row, string $message_map, string $reason):array {

		// скрываем сообщение от пользователя
		Helper_Threads::hideMessageList($meta_row["thread_map"], $meta_row, [$message_map], $this->user_id);

		// добавляем сообщение в очередь жалоб
		Gateway_Db_CompanyThread_MessageReportHistory::insert($message_map, $this->user_id, $reason);

		return $this->ok();
	}

	/**
	 * метод для получения последних сообщений треда и информации о нем
	 */
	public function getMessages():array {

		$thread_key = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);

		$block_id = $this->post(\Formatter::TYPE_INT, "block_id", 0);

		// получаем мету треда, если пользователь является участником треда
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess) {
			return $this->_returnError530();
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Parent message is deleted");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$meta_row = $e->getMetaRow();
		}

		// если запрашиваются сообщения в первый раз
		$is_first_open_thread = $block_id == 0;
		$dynamic_obj          = Type_Thread_Dynamic::get($thread_map);
		$block_id             = $this->_getLastBlockIdIfNeed($block_id, $dynamic_obj);

		// если start_block_id больше или равен последнему - то отдаем пустой массив
		if ($dynamic_obj->start_block_id >= $dynamic_obj->last_block_id) {

			$this->action->users(Type_Thread_Meta::getActionUsersList($meta_row));
			return $this->ok([
				"thread_meta" => (object) $this->_getFormattedThreadMeta($meta_row),
			]);
		}

		$block_list = $this->_getBlockList($thread_map, $dynamic_obj, $block_id);

		// подготавливаем сообщения, оставляем только доступные пользователю, а также добавляем actions
		$message_list      = $this->_formatMessageListForOutput($thread_map, $block_list);
		$previous_block_id = $this->_getPreviousBlockIdForGetMessages($dynamic_obj, $block_list, $block_id);
		$this->action->users(Type_Thread_Meta::getActionUsersList($meta_row));
		$this->_addUserToThreadIfNeed($is_first_open_thread, $thread_map);

		return $this->_returnOkGetMessages($meta_row, $message_list, $previous_block_id, $dynamic_obj, $thread_map);
	}

	// получаем block_id
	protected function _getLastBlockIdIfNeed(int $block_id, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj):int {

		// если
		// - block_id = 0
		// - block_id меньше или равен id первого блока
		// - block_id больше id последнего блока
		// делаем его равным последнему
		if ($block_id == 0 || $block_id <= $dynamic_obj->start_block_id || $block_id > $dynamic_obj->last_block_id) {
			$block_id = $dynamic_obj->last_block_id;
		}

		return $block_id;
	}

	// получаем список горячих блоков
	protected function _getBlockList(string $thread_map, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, int $block_id):array {

		// получаем список id блоков
		$block_id_list = $this->_getBlockIdList($dynamic_obj, $block_id);

		return Type_Thread_Message_Block::getList($thread_map, $block_id_list);
	}

	// подготавливаем сообщения, оставляем только доступные пользователю, а также добавляем actions
	// @long
	protected function _formatMessageListForOutput(string $thread_map, array $block_list):array {

		$message_list = [];

		// проходим каждому сообщению из горячего блока
		foreach ($block_list as $block_row) {

			try {
				$reaction_count_block_row = Gateway_Db_CompanyThread_MessageBlockReactionList::getOne($thread_map, $block_row["block_id"]);
			} catch (\cs_RowIsEmpty) {
				$reaction_count_block_row = [];
			}

			foreach ($block_row["data"] as $message) {

				if (Domain_Thread_Entity_Message::isInvisibleForUser($this->user_id, $message)) {
					continue;
				}

				$message_map = Type_Thread_Message_Main::getHandler($message)::getMessageMap($message);

				// получаем реакции для сообщения из блока
				[$message_reaction_list, $last_reaction_updated_at_ms] = $this->_getReactionList($reaction_count_block_row, $message_map);

				// форматируем сообщение и добавляем пользователей для actions
				$prepared_message = Type_Thread_Message_Main::getHandler($message)::prepareForFormat(
					$message, $message_reaction_list, $last_reaction_updated_at_ms
				);
				$message_list[]   = Apiv1_Format::threadMessage($prepared_message);

				// добавляем пользователей к ответу
				$users = Type_Thread_Message_Main::getHandler($message)::getUsers($message);
				$this->action->users($users);
			}
		}

		return $message_list;
	}

	/**
	 * получаем реакции для сообщения
	 */
	protected function _getReactionList(mixed $reaction_count_block_row, string $message_map):array {

		if (is_array($reaction_count_block_row) && count($reaction_count_block_row) < 1) {
			return [[], 0];
		}

		return Domain_Thread_Entity_MessageBlock_Reaction::fetchMessageReactionData($reaction_count_block_row, $message_map);
	}

	// получаем предыдущий block_id
	protected function _getPreviousBlockIdForGetMessages(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, array $block_list, int $block_id):int {

		// получаем список id блоков
		$block_id_list = $this->_getBlockIdList($dynamic_obj, $block_id);
		$min_block_id  = $this->_getMinBlockId($dynamic_obj, $block_id, $block_id_list);

		// получаем минимальный block_id из тех что у нас есть
		foreach ($block_list as $block_row) {

			if ($block_row["block_id"] <= $min_block_id) {
				$min_block_id = $block_row["block_id"];
			}
		}

		return $this->_getPreviousBlockId($min_block_id, $dynamic_obj);
	}

	// добавляем пользователя к слушающим тред, если необходимо
	protected function _addUserToThreadIfNeed(bool $is_first_open_thread, string $thread_map):void {

		// если запрашиваются сообщения первый раз
		if ($is_first_open_thread) {

			// отсылаем на go_talking_handler запрос, о том, что пользователь открыл тред, необходимо привязать его к треду
			Gateway_Bus_Sender::addUsersToThread([$this->user_id], $thread_map);
		}
	}

	/**
	 * Возвращаем успешный вывод для метода getMessages
	 */
	protected function _returnOkGetMessages(array                                 $meta_row, array $message_list, int $previous_block_id,
							    Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, string $thread_map):array {

		$follower_row          = Type_Thread_Followers::get($thread_map);
		$is_follow             = Type_Thread_Followers::isFollowUser($this->user_id, $follower_row);
		$is_in_unfollow_list   = Type_Thread_Followers::isUserWasUnfollow($this->user_id, $follower_row);
		$thread_menu_row       = Type_Thread_Menu::get($this->user_id, $thread_map);
		$is_favorite           = isset($thread_menu_row["is_favorite"]) && Type_Thread_Utils::isFavorite($thread_menu_row);
		$last_read_message_key = isset($thread_menu_row["last_read_message_map"])
			? \CompassApp\Pack\Message\Thread::doEncrypt($thread_menu_row["last_read_message_map"])
			: "";

		// получаем стандартные поля ответа
		$output = $this->_getOutput($meta_row, $dynamic_obj, $is_follow, $is_favorite, $is_in_unfollow_list, $last_read_message_key);

		if (count($message_list) > 0) {
			$output["message_list"] = (array) $message_list;
		}
		if ($previous_block_id > 0) {
			$output["previous_block_id"] = (int) $previous_block_id;
		}

		return $this->ok($output);
	}

	/**
	 * Получаем дефолтные поля ответа
	 */
	protected function _getOutput(array $meta_row, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj,
						bool  $is_follow, bool $is_favorite, bool $is_in_unfollow_list, string $last_read_message_key):array {

		return [
			"thread_meta"           => (object) $this->_getFormattedThreadMeta($meta_row),
			"is_follow"             => (int) $is_follow ? 1 : 0,
			"is_muted"              => (int) Type_Thread_Dynamic::isMuted($this->user_id, $dynamic_obj->user_mute_info) ? 1 : 0,
			"is_favorite"           => (int) $is_favorite ? 1 : 0,
			"is_readonly"           => (int) $meta_row["is_readonly"],
			"follow_status"         => (int) $this->_getFollowStatus($is_follow, $is_in_unfollow_list),
			"last_read_message_key" => (string) $last_read_message_key,
		];
	}

	// получаем статус треда для пользователя
	protected function _getFollowStatus(bool $is_follow, bool $is_unfollow):int {

		// если на диалог подписались
		if ($is_follow) {
			return 1;
		}

		// если на диалог были подписаны ранее
		if ($is_unfollow) {
			return 2;
		}

		return 0;
	}

	/**
	 * метод для отписки от треда
	 */
	public function doUnfollow():array {

		$thread_key = $this->post(\Formatter::TYPE_STRING, "thread_key", false);

		// если тред еще не создан
		$parent_entity_type = $this->post(\Formatter::TYPE_INT, "type", false);
		if ($parent_entity_type !== false) {
			$parent_entity_id = $this->_getParentEntityId($parent_entity_type);
		} else {
			$parent_entity_id = false;
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_UNFOLLOW, "threads", "row344");

		// проверяем, что пользователь участник треда
		try {
			Domain_Thread_Scenario_Api::unfollow($this->user_id, $thread_key, $parent_entity_type, $parent_entity_id);
		} catch (cs_Thread_UserNotMember) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->ok();
		}

		return $this->ok();
	}

	/**
	 * метод для подписки на тред
	 */
	public function doFollow():array {

		$thread_key = $this->post(\Formatter::TYPE_STRING, "thread_key", false);

		// если тред еще не создан
		$parent_entity_type = $this->post(\Formatter::TYPE_INT, "type", false);
		if ($parent_entity_type !== false) {
			$parent_entity_id = $this->_getParentEntityId($parent_entity_type);
		} else {
			$parent_entity_id = false;
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_FOLLOW, "threads", "row325");

		// подписываем на тред
		try {
			Domain_Thread_Scenario_Api::follow($this->user_id, $thread_key, $parent_entity_type, $parent_entity_id);
		} catch (cs_Thread_UserNotMember) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->error(530, "this user does not have access to this thread");
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParseFatalException("empty message list for add thread");
		}

		return $this->ok();
	}

	/**
	 * делаем репост из треда в диалог
	 *
	 * @throws \parseException|\paramException|cs_DecryptHasFailed
	 */
	public function addRepostToConversation():array {

		$conversation_key  = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$conversation_map  = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		$client_message_id = $this->post(\Formatter::TYPE_STRING, "client_message_id");
		$text              = $this->post(\Formatter::TYPE_STRING, "text", "");
		$is_attach_parent  = $this->post(\Formatter::TYPE_INT, "is_attach_parent", 0);
		$is_new_add_repost = $this->post(\Formatter::TYPE_INT, "is_new_add_repost", 0);

		// в зависимости от версии метода
		if ($is_new_add_repost == 1 || Type_System_Legacy::isAddRepostQuote() || Type_System_Legacy::isAddRepostV2()) {
			return $this->_addRepostToConversationNew($conversation_map, $client_message_id, $text, $is_attach_parent);
		}

		return $this->_addRepostToConversationLegacy($conversation_map, $client_message_id, $text, $is_attach_parent);
	}

	// делаем репост из треда в диалог
	// @long
	protected function _addRepostToConversationNew(string $conversation_map, string $client_message_id, string $text, int $is_attach_parent):array {

		if (Type_System_Legacy::isAddRepostQuote() || Type_System_Legacy::isAddRepostV2()) {
			$message_key_list = $this->post(\Formatter::TYPE_JSON, "message_key_list");
		} else {
			$message_key_list = $this->post(\Formatter::TYPE_ARRAY, "message_key_list");
		}

		$message_map_list = $this->_tryGetMessageMapList($message_key_list);

		$text = Type_Api_Filter::replaceEmojiWithShortName($text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			return $this->error(540, "Message to long");
		}

		$text              = Type_Api_Filter::sanitizeMessageText($text);
		$client_message_id = Type_Api_Filter::sanitizeClientMessageId($client_message_id);

		$this->_throwIfIncorrectClientMessageId($client_message_id);
		$this->_throwIfIncorrectMessageMapList($message_map_list);

		$max_repost_messages_count = Type_System_Legacy::isAddRepostQuote() || Type_System_Legacy::isAddRepostV2()
			? self::_MAX_SELECTED_MESSAGES_COUNT : self::_MAX_SELECTED_MESSAGES_COUNT_LEGACY;
		if (count($message_map_list) > $max_repost_messages_count) {
			return $this->error(555, "exceeded the limit on the number of reposted messages");
		}

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map_list[0]);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_ADDREPOSTTOCONVERSATION, "threads", "row361");

		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->_returnError530();
		} catch (cs_Thread_UserNotMember) {
			return $this->_returnErrorIfUserNotMemberOfThread();
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$meta_row = $e->getMetaRow();
		}

		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);
		$this->_throwIfThreadIsLocked($dynamic_obj);

		// получаем родительское сообещние
		try {

			Domain_Member_Entity_Permission::check($this->user_id, $this->method_version, Permission::IS_REPOST_MESSAGE_ENABLED);
			$parent_message = Type_Thread_Rel_Parent::getParentMessageIfNeed($this->user_id, $meta_row, $is_attach_parent == 1);
		} catch (cs_ParentMessage_IsDeleted) {
			return $this->error(553, "the parent message was deleted");
		} catch (cs_ParentMessage_IsRespect) {
			throw new ParamException("Not allow repost to conversation a respect message");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// сортируем message_map_list по порядку в треде
		$message_map_list = self::_doSortMessageMapListByMessageIndex($message_map_list);

		// отправляем репост из треда в диалог версии V2
		if (Type_System_Legacy::isAddRepostV2()) {

			[$chunk_repost_message_list] = Helper_Threads::getChunkMessageList(
				$message_map_list, $dynamic_obj, $parent_message, $this->user_id
			);

			return $this->_addRepostV2($thread_map, $conversation_map, $chunk_repost_message_list, $client_message_id, $text);
		}

		$reposted_message_list = $this->_getSelectedMessageList($message_map_list, $dynamic_obj, $thread_map, true);

		// отправляем запрос для добаления сообщений в диалог
		if (Type_System_Legacy::isAddRepostQuote()) {
			return $this->_addRepost($thread_map, $conversation_map, $reposted_message_list, $client_message_id, $text, $parent_message);
		}

		return $this->_addRepostLegacy($thread_map, $conversation_map, $reposted_message_list, $client_message_id, $text, $parent_message);
	}

	// отправляем репост из треда в диалог версии V2
	protected function _addRepostV2(string $thread_map, string $conversation_map, array $repost_list, string $client_message_id, string $text):array {

		if (count($repost_list) < 1) {
			return $this->error(551, "message list for repost is empty");
		}

		try {
			$response = Gateway_Socket_Conversation::addRepostFromThreadV2($conversation_map, $repost_list, $client_message_id, $text, $this->user_id);
		} catch (SocketException $e) {
			return $this->_returnErrorSocketAddRepostFromThread($e);
		} catch (Domain_Thread_Exception_Guest_AttemptInitialThread) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "guest attempt to initial thread");
		}

		if (!isset($response["message_list"])) {
			throw new ParseFatalException(__METHOD__ . ": socket without message");
		}

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::CONVERSATION_MESSAGE, $this->user_id, count($response["message_list"]));
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_CONVERSATION_MESSAGE);

		$this->_doAfterRepost($thread_map, $conversation_map, $response["message_list"]);

		return $this->ok([
			"message_list" => (array) $response["message_list"],
		]);
	}

	// отправляем запрос на php_conversation
	protected function _addRepost(string $thread_map, string $conversation_map, array $reposted_message_list, string $client_message_id, string $text, array $parent_message_data):array {

		try {
			$response = Gateway_Socket_Conversation::addRepostFromThread(
				$conversation_map,
				$reposted_message_list,
				$client_message_id,
				$text,
				$this->user_id,
				$parent_message_data
			);
		} catch (SocketException $e) {
			return $this->_returnErrorSocketAddRepostFromThread($e);
		} catch (Domain_Thread_Exception_Guest_AttemptInitialThread) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "guest attempt to initial thread");
		}

		if (!isset($response["message"])) {
			throw new ParseFatalException(__METHOD__ . ": socket without message");
		}

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::CONVERSATION_MESSAGE, $this->user_id);
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_CONVERSATION_MESSAGE);

		$this->_doAfterRepost($thread_map, $conversation_map, [$response["message"]]);

		return $this->ok([
			"message" => (array) $response["message"],
		]);
	}

	// отправляем запрос на php_conversation
	protected function _addRepostLegacy(string $thread_map, string $conversation_map, array $reposted_message_list, string $client_message_id, string $text, array $parent_message_data):array {

		// отправляем socket запрос
		try {
			$response = Gateway_Socket_Conversation::addRepostFromThreadBatchingLegacy($conversation_map, $reposted_message_list, $client_message_id, $text,
				$this->user_id, $parent_message_data);
		} catch (SocketException $e) {
			return $this->_returnErrorSocketAddRepostFromThread($e);
		} catch (Domain_Thread_Exception_Guest_AttemptInitialThread) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "guest attempt to initial thread");
		}

		if (!isset($response["message_list"])) {
			throw new ParseFatalException(__METHOD__ . ": socket without message");
		}

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::CONVERSATION_MESSAGE, $this->user_id);
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_CONVERSATION_MESSAGE);

		$this->_doAfterRepost($thread_map, $conversation_map, $response["message_list"]);

		return $this->ok([
			"message_list" => (array) $response["message_list"],
		]);
	}

	/**
	 * обработка ошибок из сокета
	 *
	 * @param SocketException $e
	 *
	 * @long - switch ... case
	 *
	 * @return array
	 * @throws \blockException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _returnErrorSocketAddRepostFromThread(SocketException $e):array {

		switch (get_class($e)) {

			case Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled::class: // пользователь не может писать в сингл диалог пользователь заблокировал/заблокирован другого(им) пользователя

				// достаем значение allow_status
				$allow_status = $e->getExtra()["allow_status"];

				if (Type_System_Legacy::isNewErrors()) {
					return $this->_returnErrorOnOpponentIsBlockedOrDisabled($allow_status);
				}

				return $this->error(527, "You can't write to this conversation because one of participants blocked another", [
					"allow_status" => (int) $allow_status,
				]);

			case Gateway_Socket_Exception_Conversation_BlockedByOpponent::class: // пользователь заблокировал нас
				return $this->error(904, "You have blocked by opponent");

			case Gateway_Socket_Exception_Conversation_BlockedAnOpponent::class: // мы заблокировали пользователя
				return $this->error(905, "You have blocked an opponent");

			case Gateway_Socket_Exception_Conversation_OpponentBlockedInSystem::class: // пользователь заблокирован в системе
				return $this->error(532, "You can't write to this conversation because your opponent is blocked in our system");

			case Gateway_Socket_Exception_Conversation_UserIsNotMember::class: // пользователь не участник диалога

				return $this->error(501, "User is not conversation member");
			case Gateway_Socket_Exception_Conversation_IsNotAllowed::class: // неподходящий тип диалога для действия

				throw new ParamException("Conversation type is not valid for action");

			case Gateway_Socket_Exception_Conversation_IsLocked::class: // диалог заблокирован
				throw new BlockException("Conversation is locked");

			case Gateway_Socket_Exception_Conversation_DuplicateMessageClientId::class:

				if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
					return $this->error(541, "duplicate client_message_id");
				}
				throw new ParamException("passed duplicate client message id");

			default:

				throw new ParseFatalException("Failure socket request");
		}
	}

	// выполняем после репоста
	protected function _doAfterRepost(string $thread_map, string $conversation_map, array $response_message_list):void {

		$message_map_list = [];
		foreach ($response_message_list as $v) {
			$message_map_list[] = $v["message_map"];
		}

		// добавляем запись с историей о репосте сообщений из треда в диалог
		Type_Thread_RepostRel::addList($thread_map, $conversation_map, $message_map_list, $this->user_id);
	}

	// делаем репост из треда в диалог
	// @long
	protected function _addRepostToConversationLegacy(string $conversation_map, string $client_message_id, string $text, int $is_attach_parent):array {

		$message_key_list = $this->post(\Formatter::TYPE_ARRAY, "message_key_list");

		$text = Type_Api_Filter::replaceEmojiWithShortName($text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			return $this->error(540, "Message to long");
		}

		$text              = Type_Api_Filter::sanitizeMessageText($text);
		$client_message_id = Type_Api_Filter::sanitizeClientMessageId($client_message_id);

		$this->_throwIfIncorrectClientMessageId($client_message_id);
		$this->_throwIfMessageKeyListIsInvalidLegacy($message_key_list);

		$message_map_list = $this->_tryGetMessageMapListLegacy($message_key_list);
		$thread_map       = \CompassApp\Pack\Message\Thread::getThreadMap($message_map_list[0]);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_ADDREPOSTTOCONVERSATION, "threads", "row361");

		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->_returnError530();
		} catch (cs_Thread_UserNotMember) {

			if (Type_System_Legacy::isNewErrorIfNotAccessToParentEntity()) {
				return $this->error(501, "User is not donor-conversation's member");
			}
			return $this->_returnError530();
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$meta_row = $e->getMetaRow();
		}

		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);
		$this->_throwIfThreadIsLockedLegacy($dynamic_obj);

		try {
			$reposted_message_list = $this->_getRepostedMessageListLegacy($message_map_list, $dynamic_obj, $thread_map);
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Message is deleted");
		}

		return $this->_sendRepostToConversationLegacy($thread_map, $conversation_map, $reposted_message_list, $client_message_id, $text, $meta_row, $is_attach_parent);
	}

	// получаем message_map_list
	protected function _tryGetMessageMapListLegacy(array $message_key_list):array {

		$message_map_list = [];
		$thread_map_list  = [];
		foreach ($message_key_list as $message_key) {

			// получаем message_map и thread_map
			$message_map = $this->_tryGetMessageMapLegacy($message_key);
			$this->_throwIfMessageMapIsNotFromThread($message_map);
			$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

			// проверяем что сообщения присланы из одного треда
			$thread_map_list[$thread_map] = 1;
			if (count($thread_map_list) != 1) {
				throw new ParamException("This message doesn't from the thread");
			}

			// дополняем сообщения
			$message_map_list[] = $message_map;
		}

		return $message_map_list;
	}

	// проверяем, что message_key_list валиден
	protected function _throwIfMessageKeyListIsInvalidLegacy(array $message_key_list):void {

		// проверяем что массив сообщений не пустой
		if (count($message_key_list) < 1) {
			throw new ParamException("Empty array of reposted messages");
		}

		//  проверяем количество переданных в репосте сообщений не больше 15
		if (count($message_key_list) > self::_MAX_REPOST_MESSAGES_COUNT_LEGACY) {
			throw new ParamException("Overflow the limit of reposted messages");
		}

		// проверяем что нет одинаковых сообщений
		$message_key_list_uniq = array_unique($message_key_list);
		if (count($message_key_list_uniq) != count($message_key_list)) {
			throw new ParamException("Messages can not be duplicated");
		}
	}

	// получаем message_map из message_key
	protected function _tryGetMessageMapLegacy(string $message_key):string {

		return \CompassApp\Pack\Message\Thread::tryDecrypt($message_key);
	}

	// проверяем не закрыт ли тред сейчас
	protected function _throwIfThreadIsLockedLegacy(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj):void {

		// проверяем не закрыт ли тред сейчас
		if ($dynamic_obj->is_locked == 1) {
			throw new BlockException(__METHOD__ . " thread is locked");
		}
	}

	// получаем сообщения для репоста
	protected function _getRepostedMessageListLegacy(array $message_map_list, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, string $thread_map):array {

		$reposted_message_list = []; // массив сообщений
		$block_list            = []; // массив полученных блоков (для того чтобы не обращаться несколько раз в базу за одним блоком)

		// получаем сообщения для репоста, проверяем их доступность для репоста, вычленяем параметры
		foreach ($message_map_list as $message_map) {

			// достаем блок сообщений
			$block_id = \CompassApp\Pack\Message\Thread::getBlockId($message_map);
			if (!isset($block_list[$block_id])) {
				$block_list[$block_id] = $this->_getMessageBlockLegacy($dynamic_obj, $block_id, $thread_map);
			}

			// получаем сообщение
			$message = Type_Thread_Message_Block::getMessage($message_map, $block_list[$block_id]);

			// проверяем, можно ли репостить сообщение
			$this->_throwIfMessageNotAllowToRepostLegacy($message);

			// вносим сообщение для репоста в массив
			$reposted_message_list[] = Type_Thread_Message_Handler_Default::makeThreadMessageDataForRepost($message);
		}

		return $this->_sortRepostedMessageListLegacy($reposted_message_list);
	}

	// получаем блок с сообщениями
	protected function _getMessageBlockLegacy(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, int $block_id, string $thread_map):array {

		// блок существует?
		$this->_throwIfMessageBlockNotExistLegacy($dynamic_obj, $block_id);

		// блок горячий?
		if (Type_Thread_Message_Block::isActive($dynamic_obj, $block_id)) {

			// получаем блок из базы
			$block_row = Type_Thread_Message_Block::get($thread_map, $block_id);
		} else {
			throw new ParamException(__CLASS__ . ": this message block is not exist");
		}

		// нужного блока нет в полученной записи
		if (!isset($block_row["block_id"])) {
			throw new ParamException("this message block is not exist");
		}

		return $block_row;
	}

	// проверяем, что блок с сообщениями существует
	protected function _throwIfMessageBlockNotExistLegacy(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, int $block_id):void {

		if (!Type_Thread_Message_Block::isExist($dynamic_obj, $block_id)) {
			throw new ParamException("this message block is not exist");
		}
	}

	// проверяем, что сообщение доступно для репоста
	protected function _throwIfMessageNotAllowToRepostLegacy(array $message):void {

		// если сообщение удалено
		if (Type_Thread_Message_Main::getHandler($message)::isMessageDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}

		if (!Type_Thread_Message_Main::getHandler($message)::isAllowToRepost($message, $this->user_id)) {
			throw new ParamException("User don't have permissions to repost this message");
		}
	}

	// сортируем сообщения для репоста по индексу
	protected function _sortRepostedMessageListLegacy(array $reposted_message_list):array {

		// сортируем сообщения в порядке их написания
		uasort($reposted_message_list, function(array $a, array $b) {

			return $a["message_index"] > $b["message_index"] ? 1 : -1;
		});

		return $reposted_message_list;
	}

	// отправляем репост в диалог
	protected function _sendRepostToConversationLegacy(string $thread_map, string $conversation_map, array $reposted_message_list, string $client_message_id, string $text, array $meta_row, int $is_attach_parent):array {

		$parent_message_data = [];
		if ($is_attach_parent) {
			$parent_message_data = $this->_getParentMessageLegacy($meta_row);
		}

		// отправляем socket запрос
		try {
			[$status, $response] = $this->_sendSocketRequestAddRepostFromThreadLegacy($conversation_map, $reposted_message_list, $client_message_id, $text, $parent_message_data);
		} catch (SocketException $e) {
			return $this->_returnErrorSocketAddRepostFromThreadLegacy($e);
		}

		if (!isset($response["message"])) {
			throw new ParseFatalException(__METHOD__ . ": socket without message");
		}

		if ($status != "ok") {
			throw new ParamException($response["message"]);
		}

		// добавляем запись с историей о репосте сообщений из треда в диалог
		$message_map = $response["message"]["message_map"];
		Type_Thread_RepostRel::add($thread_map, $conversation_map, $message_map, $this->user_id);

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::CONVERSATION_MESSAGE, $this->user_id);
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_CONVERSATION_MESSAGE);

		return $this->ok([
			"message" => (object) $response["message"],
		]);
	}

	// получаем родительское сообщения треда
	protected function _getParentMessageLegacy(array $meta_row):array {

		$parent_type = Type_Thread_ParentRel::getType($meta_row["parent_rel"]);
		$parent_map  = Type_Thread_ParentRel::getMap($meta_row["parent_rel"]);
		return match ($parent_type) {

			PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE => $this->_getMessageFromConversationLegacy($this->user_id, $parent_map),
			default                                 => throw new ParseFatalException("Unknown parent type"),
		};
	}

	/**
	 * Получаем сообщение из диалога
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \paramException
	 */
	protected function _getMessageFromConversationLegacy(int $user_id, string $message_map):array {

		// делаем сокет запрос и получаем сообщение
		try {
			$response = Gateway_Socket_Conversation::getMessageData($user_id, $message_map);
		} catch (Gateway_Socket_Exception_Conversation_MessageHaveNotAccess) {
			throw new ParamException("User not have permissions for repost this message");
		} catch (Gateway_Socket_Exception_Conversation_IsNotAllowed) {
			throw new ParamException("Message is not exist");
		}

		return $response["message_data"]["message"];
	}

	// отправляем сокет запрос на отправку репоста в тред
	protected function _sendSocketRequestAddRepostFromThreadLegacy(string $conversation_map, array $reposted_message_list, string $client_message_id, string $text, array $message_data):array {

		return Gateway_Socket_Conversation::doCall("conversations.addRepostFromThread", [
			"conversation_map"      => $conversation_map,
			"reposted_message_list" => $reposted_message_list,
			"client_message_id"     => $client_message_id,
			"text"                  => $text,
			"parent_message_data"   => $message_data,
		], $this->user_id);
	}

	// возвращаем ошибку при неудачном запросе
	// @long - switch
	protected function _returnErrorSocketAddRepostFromThreadLegacy(SocketException $e):array {

		switch (get_class($e)) {

			case Gateway_Socket_Exception_Conversation_BlockedByOpponent::class: // пользователь не может писать в сингл диалог пользователь заблокировал/заблокирован другого(им) пользователя

				// достаем значение allow_status
				$allow_status = $e->getExtra()["allow_status"];

				if (Type_System_Legacy::isNewErrors()) {
					return $this->_returnErrorOnOpponentIsBlockedOrDisabled($allow_status);
				}

				return $this->error(527, "You can't write to this conversation because one of participants blocked another", [
					"allow_status" => (int) $allow_status,
				]);
			case Gateway_Socket_Exception_Conversation_UserIsNotMember::class: // пользователь не участник диалога

				return $this->error(501, "User is not conversation member");

			case Gateway_Socket_Exception_Conversation_IsNotAllowed::class: // неподходящий тип диалога для действия

				throw new ParamException("Conversation type is not valid for action");

			case Gateway_Socket_Exception_Conversation_IsLocked::class: // диалог заблокирован
				throw new BlockException("Conversation is locked");

			case Gateway_Socket_Exception_Conversation_DuplicateMessageClientId::class:

				if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
					return $this->error(541, "duplicate client_message_id");
				}
				throw new ParamException("passed duplicate client message id");

			default:

				throw new ParseFatalException("Failure socket request");
		}
	}

	/**
	 * получает список последних поставленных реакций на сообщения треда
	 *
	 * @throws \parseException|\paramException|cs_DecryptHasFailed
	 */
	public function getMyReactions():array {

		$thread_key = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);

		// получаем список реакций
		$output = Type_Thread_Reaction_Main::getMyReactions($thread_map, $this->user_id);

		return $this->ok([
			"message_reaction_list" => (array) $output,
		]);
	}

	/**
	 * получаем список пользователей поставивших реакцию на сообщение
	 *
	 * @throws cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException|\returnException
	 */
	public function getReactionUsers():array {

		$message_key   = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map   = \CompassApp\Pack\Message::tryDecrypt($message_key);
		$reaction_name = $this->post(\Formatter::TYPE_STRING, "reaction_name");

		$this->_throwIfMessageMapIsNotFromThread($message_map);

		// проверяем существование реакции
		$reaction_name = $this->_getReactionAliasIfExist($reaction_name);

		// проверяем доступ пользователя к треду
		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		try {
			Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Conversation_IsBlockedOrDisabled) {
			// в таком случае не мешаем получать список реакций
		}

		// получаем запись со списком пользователей и проверяем ее существование
		$user_list = Type_Thread_Reaction_Main::getUserListForReaction($thread_map, $message_map, $reaction_name);
		$this->action->users($user_list);
		return $this->ok([
			"user_list" => (array) $user_list,
		]);
	}

	/**
	 * Получаем список реакций и пользователей поставивших ее на сообщение
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getReactionsUsersBatching():array {

		$message_key        = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map        = \CompassApp\Pack\Message::tryDecrypt($message_key);
		$reaction_name_list = $this->post(\Formatter::TYPE_ARRAY, "reaction_name_list");

		$this->_throwIfReactionNameListIsIncorrect($reaction_name_list);
		$this->_throwIfMessageMapIsNotFromThread($message_map);

		$reaction_short_name_list = [];
		foreach ($reaction_name_list as $item) {

			$reaction_name              = $this->_getReactionAliasIfExist($item);
			$reaction_short_name_list[] = $reaction_name;
		}

		// проверяем доступ пользователя к треду
		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		try {

			Domain_Member_Entity_Permission::check($this->user_id, $this->method_version, Permission::IS_GET_REACTION_LIST_ENABLED);
			Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Conversation_IsBlockedOrDisabled) {
			// в таком случае не мешаем получать список реакций
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// получаем список реакций и пользователей ее поставивших
		$message_reaction_uniq_user_list = Type_Thread_Reaction_Main::getUserListForReactionList($thread_map, $message_map, $reaction_short_name_list);

		// форматируем под формат ответа
		$message_reaction_list = $this->_makeReactionList($message_reaction_uniq_user_list);

		return $this->ok([
			"reaction_list" => (array) $message_reaction_list,
		]);
	}

	// выбрасываем ошибку, если список реакций некорректный
	protected function _throwIfReactionNameListIsIncorrect(array $reaction_name_list):void {

		// если пришел пустой массив реакций
		if (count($reaction_name_list) < 1) {
			throw new ParamException("passed empty reaction_name_list");
		}

		// если пришел слишком большой массив
		if (count($reaction_name_list) > self::_MAX_REACTION_COUNT) {
			throw new ParamException("passed reaction_name_list biggest than max");
		}
	}

	// формируем reaction_list для ответа
	protected function _makeReactionList(array $message_reaction_uniq_user_list):array {

		$message_reaction_list = [];
		foreach ($message_reaction_uniq_user_list as $reaction_name => $user_list) {

			$message_reaction_list[] = [
				"reaction_name" => (string) $reaction_name,
				"user_list"     => (array) $user_list,
			];

			// добавляем пользователей в action
			$this->action->users($user_list);
		}

		return $message_reaction_list;
	}

	/**
	 * метод для общего количества непрочитанных сообщений
	 *
	 */
	public function getTotalUnreadCount():array {

		// получаем количество непрочитанных для пользователя
		$total_unread_counter_list = Domain_Thread_Scenario_Api::getTotalUnreadCount($this->user_id);

		return $this->ok([
			"threads_unread_count"  => (int) $total_unread_counter_list["threads_unread_count"],
			"messages_unread_count" => (int) $total_unread_counter_list["messages_unread_count"],
		]);
	}

	/**
	 * получаем инитные значения юзера для тредов
	 *
	 */
	public function doInit():array {

		// получаем запись из dynamic юзера, создаем если её не было
		$user_dynamic_row = Type_UserThread_Dynamic::getForceExist($this->user_id);

		// инкрементим статистику и возвращаем ответ
		return $this->ok([
			"threads_unread_count"  => (int) $user_dynamic_row["thread_unread_count"],
			"messages_unread_count" => (int) $user_dynamic_row["message_unread_count"],
		]);
	}

	/**
	 * пометить тред как непрочитанный
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 * @throws BlockException
	 */
	public function setAsUnread():array {

		$thread_key           = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$thread_map           = \CompassApp\Pack\Thread::tryDecrypt($thread_key);
		$previous_message_key = $this->post(\Formatter::TYPE_STRING, "message_key", ""); // здесь так потому что есть кейс с тем что в треде одно сообщение
		$previous_message_map = mb_strlen($previous_message_key) > 0 ? \CompassApp\Pack\Message::doDecrypt($previous_message_key) : "";

		// сообщение из треда?
		if ($previous_message_map != "") {

			$this->_throwIfMessageMapIsNotFromThread($previous_message_map);

			// распаковываем message_map и получаем thread_map
			if (\CompassApp\Pack\Message\Thread::getThreadMap($previous_message_map) != $thread_map) {
				throw new ParamException("message from another thread");
			}
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_SET_AS_UNREAD);

		// помечаем тред не прочитанным
		try {
			Domain_Thread_Scenario_Api::setAsUnread($this->user_id, $thread_map, $previous_message_map);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|cs_Conversation_IsBlockedOrDisabled) {
			return $this->error(530, "this user does not have access to this thread");
		}

		return $this->ok();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем message_map_list
	protected function _tryGetMessageMapList(array $message_key_list):array {

		$message_map_list = [];
		$thread_map_list  = [];
		foreach ($message_key_list as $message_key) {

			// проверяем тип
			if (!is_string($message_key)) {
				throw new ParamException("Message key is not of type string");
			}

			// получаем message_map и thread_map
			$message_map = $this->_tryGetMessageMap($message_key);
			$this->_throwIfMessageMapIsNotFromThread($message_map);
			$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

			// проверяем что сообщения присланы из одного треда
			$thread_map_list[$thread_map] = 1;
			if (count($thread_map_list) != 1) {
				throw new ParamException("This message doesn't from the thread");
			}

			// дополняем сообщения
			$message_map_list[] = $message_map;
		}

		return $message_map_list;
	}

	// получаем message_map из message_key
	protected function _tryGetMessageMap(string $message_key):string {

		return \CompassApp\Pack\Message\Thread::tryDecrypt($message_key);
	}

	// получаем сообщение
	protected function _getMessage(string $thread_map, string $message_map):array {

		// получаем dynamic треда
		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);
		$block_id    = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

		// запрошенный блок горячий
		if (Type_Thread_Message_Block::isActive($dynamic_obj, $block_id)) {

			// получаем блок из быстрой таблицы
			$block_row = Type_Thread_Message_Block::get($thread_map, $block_id);
		} else {
			throw new ParamException(__CLASS__ . ": this message block is not exist");
		}

		// получаем message из блока
		return Type_Thread_Message_Block::getMessage($message_map, $block_row);
	}

	// получает previous_block_id на основе информации из dynamic
	// если предыдущего блока нет - возвращает 0
	protected function _getPreviousBlockId(int $block_id, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj):int {

		// id предыдущего блока < start_block_id?
		if ($block_id - 1 <= $dynamic_obj->start_block_id) {
			return 0;
		}

		return $block_id - 1;
	}

	// получаем thread_meta
	protected function _getFormattedThreadMeta(array $meta_row):array {

		// форматируем thread_meta
		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($meta_row, $this->user_id);
		return Apiv1_Format::threadMeta($prepared_thread_meta);
	}

	// получаем block_id_list
	protected function _getBlockIdList(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, int $block_id):array {

		// получаем блок с которого начинается выборка сообщений (по умолчанию с первого архивного)
		$start_block_id = $dynamic_obj->start_block_id;

		// формируем IN для блоков
		$block_id_list = range($start_block_id + 1, $block_id);

		// обрезаем IN и получаем блоки
		return array_slice($block_id_list, -1 * self::_GET_LAST_MESSAGES_BLOCK_COUNT, self::_GET_LAST_MESSAGES_BLOCK_COUNT);
	}

	// получаем минимальный block_id
	protected function _getMinBlockId(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, int $block_id, array $block_id_list):int {

		return min($block_id_list);
	}

	// получаем тред мап из списка сообщений
	protected function _getThreadMapFromMessageMapList(array $message_map_list):string {

		$thread_map_list = [];
		foreach ($message_map_list as $v) {

			if (!\CompassApp\Pack\Message::isFromThread($v)) {
				throw new ParamException("one of the messages does not from thread");
			}

			// проверяем что сообщения присланы из одного диалога
			$thread_map_list[] = \CompassApp\Pack\Message\Thread::getThreadMap($v);
			$thread_map_list   = array_unique($thread_map_list);
			if (count($thread_map_list) != 1) {
				throw new ParamException("one of the messages does not belong to the donor-thread");
			}
		}

		return $thread_map_list[0];
	}

	// если тред доступен для отправки, то отправляем список сообщений
	// @long поддержка легаси + большой блок try
	protected function _addMessageList(string $thread_map, array $meta_row, array $client_message_list):array {

		[$raw_message_list, $mentioned_users] = $this->_generateRawMessageList($client_message_list, $meta_row);

		// создаем сообщения и проверяем, может ли пользователь писать в тред
		try {
			$data = Domain_Thread_Action_Message_AddList::do($thread_map, $meta_row, $raw_message_list, $mentioned_users, [], []);
		} catch (cs_ThreadIsLocked) {
			throw new BlockException("Thread is locked");
		} catch (cs_ThreadIsReadOnly) {
			return $this->error(533, "Thread is read only");
		} catch (cs_Message_DuplicateClientMessageId) {

			if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
				return $this->error(541, "duplicate client_message_id");
			}
			throw new ParamException("client_message_id is duplicated");
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParamException("empty message list");
		}

		$prepared_thread_meta  = Type_Thread_Utils::prepareThreadMetaForFormat($data["meta_row"], $this->user_id);
		$prepared_message_list = [];

		foreach ($data["message_list"] as $v) {

			$prepared_message        = Type_Thread_Message_Main::getHandler($v)::prepareForFormat($v);
			$prepared_message_list[] = (object) Apiv1_Format::threadMessage($prepared_message);
		}

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::THREAD_MESSAGE, $this->user_id, count($prepared_message_list));
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_THREAD_MESSAGE);

		// если легаси вызов, то нужно только одно сообщение
		if (!Type_System_Legacy::isLongMessageSupported()) {

			return $this->ok([
				"thread_meta" => (object) Apiv1_Format::threadMeta($prepared_thread_meta),
				"message"     => (object) reset($prepared_message_list),
			]);
		}

		return $this->ok([
			"thread_meta"  => (object) Apiv1_Format::threadMeta($prepared_thread_meta),
			"message_list" => (array) $prepared_message_list,
		]);
	}

	/**
	 * создаем массив сообщений-заготовок перед созданием записей в базу
	 *
	 * @throws cs_PlatformNotFound
	 * @throws \parseException
	 */
	protected function _generateRawMessageList(array $client_message_list, array $meta_row):array {

		$raw_message_list = [];
		$mentioned_users  = [];

		// оставшиеся сообщения имеют тип текст
		foreach ($client_message_list as $v) {

			$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($meta_row, $v["text"]);
			$mentioned_users[]    = $mention_user_id_list;

			if ($v["file_map"] !== false) {

				$message = Type_Thread_Message_Main::getLastVersionHandler()::makeFile(
					$this->user_id, $v["text"], $v["client_message_id"], $v["file_map"], $v["file_name"], Type_Api_Platform::getPlatform()
				);
			} else {

				$message = Type_Thread_Message_Main::getLastVersionHandler()::makeText(
					$this->user_id, $v["text"], $v["client_message_id"], [], Type_Api_Platform::getPlatform()
				);
			}

			$raw_message_list[] = Type_Thread_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);
		}

		// сделано так чтобы разом подписать всех :)
		$mentioned_users = array_merge(...$mentioned_users);

		return [$raw_message_list, $mentioned_users];
	}

	/**
	 * фиксируем затраченное время сотрудника
	 *
	 * @throws \blockException
	 * @throws cs_ParentMessage_IsRespect
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function doCommitWorkedHours():array {

		$message_key_list = $this->post(\Formatter::TYPE_JSON, "message_key_list");
		$message_map_list = $this->_tryGetMessageMapList($message_key_list);
		$worked_hours     = $this->post(\Formatter::TYPE_FLOAT, "worked_hours");
		$is_attach_parent = $this->post(\Formatter::TYPE_INT, "is_attach_parent", 0);

		// проверяем, что передали корректный worked_hours
		$this->_throwIfIncorrectWorkedHours($worked_hours);

		// проверяем, что передали корректный message_map_list
		$this->_throwIfIncorrectMessageMapList($message_map_list);

		if (count($message_map_list) > self::_MAX_SELECTED_MESSAGES_COUNT) {
			return $this->error(555, "exceeded the limit on the number of selected messages");
		}

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map_list[0]);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_DOCOMMITWORKEDHOURS, "threads", "row725");

		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->_returnError530();
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$meta_row = $e->getMetaRow();
		}

		$this->_throwIfThreadMetaIsNotConversationEntity($meta_row["source_parent_rel"]);

		// проверяем, что диалог, в котором существует тред, вообще пригоден для фиксации рабочего времени
		if (!$this->_isConversationOfThreadIsCanCommitWorkedHours($meta_row["source_parent_rel"])) {
			return $this->error(610, "conversation option for commit worked hours is disabled");
		}

		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);
		$this->_throwIfThreadIsLocked($dynamic_obj);

		$selected_message_list = $this->_getSelectedMessageList($message_map_list, $dynamic_obj, $thread_map, true, true);

		try {
			$parent_message_data = Type_Thread_Rel_Parent::getParentMessageIfNeed($this->user_id, $meta_row, $is_attach_parent == 1);
		} catch (cs_ParentMessage_IsDeleted) {
			return $this->error(553, "the parent message was deleted");
		}

		return $this->_tryCommitWorkedHours($worked_hours, $selected_message_list, $parent_message_data);
	}

	// выбрасываем \paramException, если передали некорректное значение worked_hours
	protected function _throwIfIncorrectWorkedHours(float $worked_hours):void {

		if ($worked_hours < 0 || $worked_hours > 48) {
			throw new ParamException(__METHOD__ . ": passed incorrect worked hours value");
		}
	}

	// проверяем, что диалог, в котором существует тред, вообще пригоден для фиксации рабочего времени
	protected function _isConversationOfThreadIsCanCommitWorkedHours(array $source_parent_rel):bool {

		$conversation_map = Type_Thread_SourceParentRel::getMap($source_parent_rel);
		return Gateway_Socket_Conversation::isCanCommitWorkedHours($conversation_map);
	}

	// пытаемся зафиксировать время
	protected function _tryCommitWorkedHours(float $worked_hours, array $selected_message_list, array $parent_message_data):array {

		// пытаемся зафиксировать время
		[$status, $response] = Gateway_Socket_Conversation::tryCommitWorkedHoursFromThread($this->user_id, $worked_hours, $selected_message_list, $parent_message_data);
		if ($status != "ok") {
			return $this->_returnErrorSocketOnFailCommitWorkedHours($response);
		}
		if (!isset($response["message_list"], $response["user_list"])) {
			throw new ParseFatalException(__METHOD__ . ": unexpected response");
		}

		// добавляем пользователей к ответу
		$this->action->users($response["user_list"]);

		return $this->ok([
			"message_list" => (array) $response["message_list"],
		]);
	}

	// возвращаем ошибку в зависимости от результатов socket-запроса для фиксации рабочего времени
	protected function _returnErrorSocketOnFailCommitWorkedHours(array $response):array {

		// достигли лимита по выбранным сообщениям
		if ($response["error_code"] == 6000) {
			return $this->error(555, "exceeded the limit on the number of selected messages");
		}

		throw new ParseFatalException("unhandled error_code");
	}

	/**
	 * пробуем проявить требовательность
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \apiAccessException
	 * @throws \blockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function tryExacting():array {

		$user_id_list     = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");
		$message_key_list = $this->post(\Formatter::TYPE_JSON, "message_key_list");
		$message_map_list = $this->_tryGetMessageMapList($message_key_list);
		$is_attach_parent = $this->post(\Formatter::TYPE_INT, "is_attach_parent", 0);

		// проверяем параметры на корректность
		try {

			$this->_throwIfIncorrectParamsForExacting($user_id_list, $this->user_id);
		} catch (Domain_Thread_Exception_User_IsAccountDeleted) {
			return $this->error(2129001, "You can't write to this conversation because your opponent delete account");
		}

		// проверяем, корректный ли user_id_list
		if (count($user_id_list) < 1 || count($user_id_list) > self::_MAX_USER_ID_LIST_FOR_EXACTINGNESS) {
			throw new ParamException("incorrect param user_id_list");
		}

		// проверяем, что передали корректный message_map_list
		$this->_throwIfIncorrectMessageMapList($message_map_list);
		if (count($message_map_list) > self::_MAX_SELECTED_MESSAGES_COUNT) {
			return $this->error(555, "exceeded the limit on the number of selected messages");
		}

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map_list[0]);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_TRYEXACTING);

		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $this->user_id);
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->_returnError530();
		} catch (cs_Thread_UserNotMember) {
			return $this->_returnErrorIfUserNotMemberOfThread();
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$meta_row = $e->getMetaRow();
		}

		$this->_throwIfThreadMetaIsNotConversationEntity($meta_row["source_parent_rel"]);

		$dynamic_obj = Type_Thread_Dynamic::get($thread_map);
		$this->_throwIfThreadIsLocked($dynamic_obj);

		$selected_message_list = $this->_getSelectedMessageList($message_map_list, $dynamic_obj, $thread_map, true, true);

		try {
			$parent_message_data = Type_Thread_Rel_Parent::getParentMessageIfNeed($this->user_id, $meta_row, $is_attach_parent == 1);
		} catch (cs_ParentMessage_IsDeleted) {
			return $this->error(553, "the parent message was deleted");
		} catch (cs_ParentMessage_IsRespect) {
			throw new ParamException("Not allow quote exacting respect message");
		}

		return $this->_tryExacting($user_id_list, $selected_message_list, $parent_message_data);
	}

	/**
	 * Выбрасываем исключение, если передали некорректные параметры
	 *
	 * @throws Domain_Thread_Exception_User_IsAccountDeleted
	 * @throws ParamException
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 */
	protected function _throwIfIncorrectParamsForExacting(array $user_id_list, int $user_id):void {

		// проверяем, корректный ли user_id_list
		if (count($user_id_list) < 1 || count($user_id_list) > self::_MAX_USER_ID_LIST_FOR_EXACTINGNESS) {
			throw new ParamException("incorrect param user_id_list");
		}

		$user_info_list = Gateway_Bus_CompanyCache::getMemberList($user_id_list);
		if (count($user_info_list) < count($user_id_list)) {
			throw new ParamException("dont found user in company cache");
		}

		// проверяем что можем выписать требовательность
		foreach ($user_info_list as $member) {

			if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
				throw new Domain_Thread_Exception_User_IsAccountDeleted("user delete his account");
			}

			if ($member->user_id == $user_id) {
				throw new ParamException("try to exact to myself");
			}
		}
	}

	// пытаемся проявить требовательность
	protected function _tryExacting(array $user_id_list, array $selected_message_list, array $parent_message_data):array {

		try {
			$response = Gateway_Socket_Conversation::tryExactingFromThread($this->user_id, $user_id_list, $selected_message_list, $parent_message_data);
		} catch (Gateway_Socket_Exception_Conversation_UserIsNotMember) {
			return $this->error(501, "user is not member of group Exactingness");
		} catch (Gateway_Socket_Exception_Conversation_MessageLimitExceeded) {
			return $this->error(555, "exceeded the limit on the number of selected messages");
		}

		if (!isset($response["conversation_map"])) {
			throw new ParseFatalException(__METHOD__ . ": unexpected response");
		}

		return $this->ok([
			"conversation_map" => (string) $response["conversation_map"],
		]);
	}

	/**
	 * Метод для получения meta и menu для тредов
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getMetaAndMenuBatching():array {

		$thread_key_list = $this->post(\Formatter::TYPE_ARRAY, "thread_key_list");

		// бросаем ошибку, если пришел некорректный массив тредов
		$this->_throwIfThreadListIsIncorrect($thread_key_list);

		// преобразуем все key в map
		$thread_map_list = $this->_tryDecryptThreadList($thread_key_list);

		[
			$frontend_thread_meta_list,
			$frontend_thread_menu_list,
			$action_user_id_list,
		] = Domain_Thread_Scenario_Api::getMetaAndMenuBatching($this->user_id, $thread_map_list);

		$this->action->users($action_user_id_list);

		return $this->ok([
			"thread_meta_list" => (array) $frontend_thread_meta_list,
			"thread_menu_list" => (array) $frontend_thread_menu_list,
		]);
	}

	/**
	 * Добавлеяем тред в избранное
	 */
	public function addToFavorite():array {

		// получаем thread_map
		$thread_key = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_ADD_TO_FAVORITE);

		// проверяем, что пользователь участник треда
		try {
			Domain_Thread_Scenario_Api::addToFavorite($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->error(530, "this user does not have access to this thread");
		} catch (cs_Thread_ToManyInFavorite) {
			return $this->error(561, "Exceeded the maximum number of favorite thread");
		}

		return $this->ok();
	}

	/**
	 * Убираем тред из избранного
	 */
	public function removeFromFavorite():array {

		// получаем thread_map
		$thread_key = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_REMOVE_FROM_FAVORITE);

		// проверяем, что пользователь участник треда
		try {
			Domain_Thread_Scenario_Api::removeFromFavorite($thread_map, $this->user_id);
		} catch (cs_Thread_UserNotMember) {
			return $this->error(530, "this user is not member of thread");
		} catch (cs_Message_HaveNotAccess|cs_Message_IsDeleted) {
			return $this->ok();
		}

		return $this->ok();
	}

	// -------------------------------------------------------
	// PROTECTED THROW
	// -------------------------------------------------------

	/**
	 * бросаем исключение если пользователь не является отправителем сообщения или сообщение нельзя удалить
	 *
	 * @param $e
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	protected function _throwIfUserNotSenderMessageOrNotAllowForDeleteMessage($e):void {

		if ($e instanceof cs_Message_UserNotSender) {
			throw new ParamException("you are not the sender of the message");
		}

		if ($e instanceof cs_Message_IsNotAllowForDelete) {
			throw new ParamException("you have not permissions to delete this message");
		}
		throw new ParseFatalException("got unknown error");
	}

	// проверяем, что message_map_list валиден
	protected function _throwIfMessageMapListIsInvalid(array $message_map_list):void {

		// проверяем что массив сообщений не пустой
		if (count($message_map_list) < 1) {
			throw new ParamException("Empty array of selected messages");
		}

		// проверяем что нет одинаковых сообщений
		$this->_throwIfMessageMapDuplicated($message_map_list);
	}

	// выбрасываем исключение, если ключи сообщения повторяются
	protected function _throwIfMessageMapDuplicated(array $message_map_list):void {

		$message_map_list_uniq = array_unique($message_map_list);
		if (count($message_map_list_uniq) != count($message_map_list)) {
			throw new ParamException("Messages can not be duplicated");
		}
	}

	// проверяем, что список не превышает допустимый лимит
	protected function _throwIfMessageMapListOverflow(array $message_map_list):void {

		if (count($message_map_list) > self::_MAX_SELECTED_MESSAGES_COUNT_LEGACY) {
			throw new ParamException("Overflow the limit of selected messages");
		}
	}

	// выбрасываем ошибку, если список тредов некорректный
	protected function _throwIfThreadListIsIncorrect(array $thread_list):void {

		// если пришел пустой массив файлов
		if (count($thread_list) < 1) {
			throw new ParamException("passed empty thread_list");
		}

		// если пришел слишком большой массив
		if (count($thread_list) > Domain_Thread_Entity_Validator::MAX_THREAD_MENU_COUNT) {
			throw new ParamException("passed thread_list biggest than max");
		}
	}

	// преобразуем пришедшие ключи в map
	protected function _tryDecryptThreadList(array $thread_list):array {

		$thread_map_list = [];
		foreach ($thread_list as $key) {

			// преобразуем key в map
			$thread_map = \CompassApp\Pack\Thread::tryDecrypt($key);

			// добавляем тред в массив
			$thread_map_list[] = $thread_map;
		}

		return $thread_map_list;
	}

	// проверяем, что message_map из треда
	protected function _throwIfMessageMapIsNotFromThread(string $message_map):void {

		if (!\CompassApp\Pack\Message::isFromThread($message_map)) {
			throw new ParamException("the message is not from thread");
		}
	}

	// проверяем, что реакция существует
	protected function _getReactionAliasIfExist(string $reaction_name):string {

		$reaction_name = Type_Thread_Reaction_Main::getReactionNameIfExist($reaction_name);
		if ($reaction_name === "") {
			throw new ParamException(__CLASS__ . ": reaction does not exist");
		}

		return $reaction_name;
	}

	// возвращаем ошибку доступа к треду
	protected function _returnError530():array {

		return $this->error(530, "User not allowed to this actions");
	}

	/**
	 * возвращаем ошибку, если пользователь не является участником треда
	 *
	 */
	protected function _returnErrorIfUserNotMemberOfThread():array {

		if (Type_System_Legacy::isNewErrorIfNotAccessToParentEntity()) {
			return $this->error(501, "User is not donor-conversation's member");
		}

		return $this->_returnError530();
	}

	// проверяем что пришел валидный client_message_id
	protected function _throwIfIncorrectClientMessageId(string $client_message_id):void {

		if (strlen($client_message_id) < 1) {
			throw new ParamException("Got incorrect client_message_id in request");
		}
	}

	// сортируем полученые сообщения по порядку нахождения их в треде
	protected static function _doSortMessageMapListByMessageIndex(array $message_map_list):array {

		$grouped_message_map_list = [];
		foreach ($message_map_list as $message_map) {

			$message_index                            = \CompassApp\Pack\Message\Thread::getThreadMessageIndex($message_map);
			$grouped_message_map_list[$message_index] = $message_map;
		}

		ksort($grouped_message_map_list);
		return array_values($grouped_message_map_list);
	}

	// выбрасываем исключение, если некорректный тред у сообщений
	protected function _throwIfIncorrectThread(array $message_map_list):void {

		foreach ($message_map_list as $v) {

			// получаем thread_map
			$this->_throwIfMessageMapIsNotFromThread($v);
			$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($v);

			// проверяем что сообщения присланы из одного треда
			$thread_map_list[$thread_map] = 1;
			if (count($thread_map_list) != 1) {
				throw new ParamException("This message doesn't from the thread");
			}
		}
	}

	// возвращаем ошибку в зависимости от полученного allow_status
	// @long - switch..case
	protected function _returnErrorOnOpponentIsBlockedOrDisabled(int $allow_status):array {

		// в зависимости от полученного allow_status — подготавливаем код и сообщение об ошибке
		switch ($allow_status) {

			case 11: // в диалог нельзя писать, собеседник заблокирован нами

				$error_code    = 905;
				$error_message = "opponent has blocked";
				break;

			case 12: // в диалог нельзя писать, мы заблокированы собеседником

				$error_code    = 904;
				$error_message = "opponent blocked us";
				break;

			case 14: // в диалог нельзя писать, один из участников заблокирован в системе

				$error_code    = 532;
				$error_message = "opponent has blocked in system";
				break;

			case 15: // в диалог нельзя писать, пользователь удалил аккаунт

				$error_code    = 2129001;
				$error_message = "opponent has delete account";
				break;

			case 20: // в диалог нельзя писать, бот выключен

				$error_code    = 2134001;
				$error_message = "userbot has disabled";
				break;

			case 21: // в диалог нельзя писать, бот удалён

				$error_code    = 2134002;
				$error_message = "userbot has deleted";
				break;

			default:
				throw new ReturnFatalException(__METHOD__ . ": passed unhandled error");
		}

		return $this->error($error_code, $error_message);
	}

	// проверяем не закрыт ли тред сейчас
	protected function _throwIfThreadIsLocked(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj):void {

		// проверяем не закрыт ли тред сейчас
		if ($dynamic_obj->is_locked == 1) {
			throw new BlockException(__METHOD__ . " thread is locked");
		}
	}

	// получаем сообщения для репоста/перессылки (например для фиксации сообщений вместе с отработанными часами в чат "Личный Heroes")
	protected function _getSelectedMessageList(array $message_map_list, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, string $thread_map, bool $is_add_repost_quote = false, bool $need_filter_special_repost = false):array {

		$block_list            = $this->_getBlockListRow($message_map_list, $dynamic_obj, $thread_map);
		$selected_message_list = [];

		// получаем сообщения для репоста/перессылки, проверяем их доступность для репоста, вычленяем параметры
		foreach ($message_map_list as $v) {

			// достаем блок сообщений
			$block_id = \CompassApp\Pack\Message\Thread::getBlockId($v);
			if (!isset($block_list[$block_id])) {
				throw new ParamException("message block not exist");
			}

			try {
				$message = $this->_getSelectedMessage($v, $block_list[$block_id]);
			} catch (cs_Message_IsDeleted) {
				continue;
			}

			// проверяем, можно ли репостить сообщение
			// такую же проверку делаем и для перессылаемых сообщений, так как логика идентична
			$this->_throwIfMessageNotAllowToRepost($message, $is_add_repost_quote);

			if ($need_filter_special_repost && Type_Thread_Message_Main::getHandler($message)::isUserbotSender($message)) {
				throw new ParamException("Can't repost this message");
			}

			// форматируем сообщение в нужный формат для репоста
			$message_index                         = \CompassApp\Pack\Message\Thread::getThreadMessageIndex($message["message_map"]);
			$selected_message_list[$message_index] = Type_Thread_Message_Handler_Default::makeThreadMessageDataForRepost($message);
		}

		if (count($selected_message_list) < 1) {
			throw new ParamException("Empty array of selected messages");
		}

		ksort($selected_message_list);
		return array_values($selected_message_list);
	}

	// получаем блоки с сообщениями
	protected function _getBlockListRow(array $message_map_list, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, string $thread_map):array {

		$active_block_id_list = [];

		foreach ($message_map_list as $v) {

			$block_id = \CompassApp\Pack\Message\Thread::getBlockId($v);
			$this->_throwIfMessageBlockNotExist($dynamic_obj, $block_id);

			// разделяем блоки на горячие и архивные
			if (Type_Thread_Message_Block::isActive($dynamic_obj, $block_id)) {
				$active_block_id_list[] = $block_id;
			}
		}

		return $this->_getBlockListRowByIdList($thread_map, $active_block_id_list);
	}

	// проверяем, что блок с сообщениями существует
	protected function _throwIfMessageBlockNotExist(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, int $block_id):void {

		if (!Type_Thread_Message_Block::isExist($dynamic_obj, $block_id)) {
			throw new ParseFatalException("this message block is not exist");
		}
	}

	// получаем блоки с сообщениями совершая запросы к базе и архивному серверу
	protected function _getBlockListRowByIdList(string $thread_map, array $active_block_id_list):array {

		// получаем горячие блоки
		// объединяем все блоки в один список
		return Type_Thread_Message_Block::getActiveBlockRowList($thread_map, $active_block_id_list);
	}

	// достаем сообщение для репоста
	protected function _getSelectedMessage(string $message_map, array $block_row):array {

		// получаем сообщение
		$message = Type_Thread_Message_Block::getMessage($message_map, $block_row);

		// если сообщение удалено
		if (Type_Thread_Message_Main::getHandler($message)::isMessageDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}

		return $message;
	}

	// проверяем, что сообщение доступно для репоста
	protected function _throwIfMessageNotAllowToRepost(array $message, bool $is_add_repost_quote):void {

		if (!Type_Thread_Message_Main::getHandler($message)::isAllowToRepost($message, $this->user_id, $is_add_repost_quote)) {
			throw new ParamException("User don't have permissions to repost this message");
		}
	}

	// выбрасываем исключение если передан некорректный список сообщений
	protected function _throwIfIncorrectMessageMapList(array $message_map_list):void {

		if (count($message_map_list) < 1) {
			throw new ParamException("messages array are empty");
		}

		// проверяем что список сообщений уникален
		$message_map_list_uniq = array_unique($message_map_list);
		if (count($message_map_list_uniq) != count($message_map_list)) {
			throw new ParamException("messages can't be duplicated");
		}

		// проверяем что сообщения присланы из одного треда
		$this->_checkThreadMapOnlyFromOneThread($message_map_list);
	}

	// проверка, что все полученные сообщения находятся в одном треде
	protected function _checkThreadMapOnlyFromOneThread(array $message_map_list):void {

		$thread_map_list = [];
		foreach ($message_map_list as $v) {

			if (!\CompassApp\Pack\Message::isFromThread($v)) {
				throw new ParamException("message not from thread");
			}

			// проверяем, что сообщения из одного треда
			$thread_map                   = \CompassApp\Pack\Message\Thread::getThreadMap($v);
			$thread_map_list[$thread_map] = 1;
			if (count($thread_map_list) != 1) {
				throw new ParamException("one of the messages does not belong to the donor-thread");
			}
		}
	}

	// выполняем цитирование массива сообщений
	// @long
	protected function _addQuoteMessage(array $meta_row, array $message_map_list, string $text, string $client_message_id, array $mention_user_id_list, int $is_attach_parent):array {

		try {

			$parent_message = Type_Thread_Rel_Parent::getParentMessageIfNeed($this->user_id, $meta_row, $is_attach_parent == 1);

			$data = Helper_Threads::addQuote($meta_row["thread_map"], $meta_row, $message_map_list, $this->user_id, $text, $client_message_id,
				$mention_user_id_list, $parent_message, Type_Api_Platform::getPlatform());
		} catch (cs_ThreadIsLocked) {
			throw new BlockException(__METHOD__ . " trying to write message in thread which is locked");
		} catch (cs_ThreadIsReadOnly) {
			return $this->error(533, "thread is read only");
		} catch (cs_MessageList_IsEmpty) {
			return $this->error(551, "message list for quote is empty");
		} catch (cs_Message_Limit) {
			return $this->error(552, "exceeded the limit on the number of selected messages for quote");
		} catch (cs_ParentMessage_IsDeleted) {
			return $this->error(553, "the parent message was deleted");
		} catch (cs_ParentMessage_IsRespect) {
			throw new ParamException("Not allow quote respect message to thread");
		} catch (cs_Message_DuplicateClientMessageId) {

			if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
				return $this->error(541, "duplicate client_message_id");
			}
			throw new ParamException("client_message_id is duplicated");
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParamException("passed empty message list");
		}

		return $this->_returnOutputForAddQuote($data);
	}

	// возвращаем ответ для цитирования
	protected function _returnOutputForAddQuote(array $data):array {

		$prepared_message     = Type_Thread_Message_Main::getHandler($data["message"])::prepareForFormat($data["message"]);
		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($data["meta_row"], $this->user_id);

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::THREAD_MESSAGE, $this->user_id);
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_THREAD_MESSAGE);

		return $this->ok([
			"message"     => (object) Apiv1_Format::threadMessage($prepared_message),
			"thread_meta" => (object) Apiv1_Format::threadMeta($prepared_thread_meta),
		]);
	}

	// выбрасываем \paramException, если meta треда не диалог
	protected function _throwIfThreadMetaIsNotConversationEntity(array $source_parent_rel):void {

		if (Type_Thread_SourceParentRel::getType($source_parent_rel) != SOURCE_PARENT_ENTITY_TYPE_CONVERSATION) {
			throw new ParamException("thread meta is not conversation entity");
		}
	}
}
