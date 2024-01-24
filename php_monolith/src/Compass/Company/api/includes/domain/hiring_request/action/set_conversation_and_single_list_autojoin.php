<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Extra;

/**
 * Класс для добавления пользователя в группы
 */
class Domain_HiringRequest_Action_SetConversationAndSingleListAutojoin {

	/**
	 * Добавляем пользователя в группы, которые добавились в заявку на наем.
	 *
	 * @throws Domain_User_Exception_AllAccountDeleted
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_AllUserKicked
	 * @throws cs_ConversationIsNotAvailableForPreset
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id, Struct_Db_CompanyData_HiringRequest $hiring_request, array $passed_conversation_key_list, array $passed_opponent_list):array {

		// получаем всех пользователей, с которым можно создать диалог
		// тут пока не учитываем уже ранее созданные диалоги
		$allowed_passed_opponent_id_list = static::_tryGetAllowedUserList($passed_opponent_list);

		// добавляем пользователя в групповые диалоги синхронно, чтобы можно было обработать ошибки
		$to_join_conversation_map_list = static::_getToJoinMapConversationList($passed_conversation_key_list, $hiring_request);
		static::_joinGroups($user_id, $hiring_request, $to_join_conversation_map_list, $allowed_passed_opponent_id_list);

		// получаем список пользователей, с которыми нужно создать диалог
		// сами диалоги создадим после обновления заявки
		$to_join_opponent_user_id_list = static::_getToJoinOpponentUserIdList($allowed_passed_opponent_id_list, $hiring_request);

		/** начало транзакции */
		Gateway_Db_CompanyData_Main::beginTransaction();

		// поскольку сокет на вступление в группы выполняться может некоторое время,
		// то заявка может измениться, и нужно убедиться, что данные диалогов
		// для записи в экстру на момент записи актуальные, а не взяты до выполнения сокетов

		try {

			// получаем запись для обновления, заодно актуализируем экстру,
			// вдруг она поменялась, пока мы там по сокетам бегали
			$hiring_request = Domain_HiringRequest_Entity_Request::getForUpdate($hiring_request->hiring_request_id);
		} catch (cs_HireRequestNotExist) {

			// кидаем фатал, поскольку проверка на существование
			// заявки должна была быть выполнена ранее
			Gateway_Db_CompanyData_Main::rollback();
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("not found hiring request's row");
		}

		// обновляем данные автоматического вступления в чаты для заявки
		$hiring_request = static::_updateRequestAutojoinData($hiring_request, $passed_conversation_key_list, $passed_opponent_list, $allowed_passed_opponent_id_list);

		Gateway_Db_CompanyData_Main::commitTransaction();
		/** завершение транзакции */

		// синглы добавляем не одновременно с группами, потому что нужна актуальная заявка после обновления
		static::_joinSingles($hiring_request, $to_join_opponent_user_id_list);

		// получаем данные и форматируем заявку для ответа
		$member_info = Gateway_Bus_CompanyCache::getMember($hiring_request->candidate_user_id);
		$user_info   = new Struct_User_Info(
			$user_id,
			$member_info->full_name,
			$member_info->avatar_file_key,
			Extra::getAvatarColorId($member_info->extra),
		);
		[$formatted_hiring_request, $action_user_id_list] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info);

		return [$formatted_hiring_request, $action_user_id_list];
	}

	/**
	 * Вступим в переданные группы
	 *
	 * @throws cs_ConversationIsNotAvailableForPreset
	 * @throws \returnException
	 * @long
	 */
	protected static function _joinGroups(int $user_id, Struct_Db_CompanyData_HiringRequest $hiring_request, array $conversation_map_list, array $single_ok_list):void {

		// отправляем запрос на вступление в группы
		$response = Gateway_Socket_Conversation::joinToGroupConversationList($user_id, $hiring_request->candidate_user_id, $conversation_map_list);
		if ($response !== false) {

			throw new cs_ConversationIsNotAvailableForPreset(
				static::_packConversationMapList($response["ok_list"]),
				static::_packConversationMapList($response["is_not_owner_list"]),
				static::_packConversationMapList($response["is_leaved_list"]),
				static::_packConversationMapList($response["is_kicked_list"]),
				static::_packConversationMapList($response["is_not_exist_list"]),
				static::_packConversationMapList($response["is_not_group_list"]),
				$single_ok_list,
				[]
			);
		}
	}

	/**
	 * Перегоняем мапы в ключи.
	 */
	protected static function _packConversationMapList(array $conversation_map_list):array {

		$conversation_key_list = [];
		foreach ($conversation_map_list as $conversation_map) {

			$conversation_key_list[] = Type_Pack_Conversation::doEncrypt($conversation_map);
		}
		return $conversation_key_list;
	}

	/**
	 * Добавляем пользвателя в сингл-диалоги
	 *
	 * Делаем асинхронно, не знаю, почему так,
	 * возможно для синглов ошибки не должны обрабатываться
	 */
	protected static function _joinSingles(Struct_Db_CompanyData_HiringRequest $hiring_request, array $to_join_opponent_user_id_list):void {

		$event = Type_Event_Conversation_AddSingleList::create(
			$hiring_request->candidate_user_id,
			$to_join_opponent_user_id_list,
			false,
			false
		);

		Gateway_Event_Dispatcher::dispatch($event, true);
	}

	/**
	 * Получим список групп для вступления пользователя в компанию
	 *
	 * @return string[]
	 * @throws \cs_DecryptHasFailed
	 */
	protected static function _getToJoinMapConversationList(array $conversation_key_list, Struct_Db_CompanyData_HiringRequest $hiring_request):array {

		// мержим списки после одобрения заявки
		[$to_join_conversation_key_list] = self::_resolveToJoinConversationKeyList($conversation_key_list, $hiring_request);

		return array_map(
			static fn(array $el) => Type_Pack_Conversation::doDecrypt($el["conversation_key"]),
			$to_join_conversation_key_list
		);
	}

	/**
	 * мержим старый и новый списки групп
	 *
	 * @param array                               $passed_conversation_key_list
	 * @param Struct_Db_CompanyData_HiringRequest $hiring_request
	 *
	 * @return array
	 */
	protected static function _resolveToJoinConversationKeyList(array $passed_conversation_key_list, Struct_Db_CompanyData_HiringRequest $hiring_request):array {

		$existing_conversation_list     = Domain_HiringRequest_Entity_Request::getConversationKeyListToJoin($hiring_request->extra);
		$existing_conversation_key_list = array_column($existing_conversation_list, "conversation_key");

		// получаем диалоги, которые отсутствовали
		// в заявке ранее, в них будем добавлять пользователя
		$to_join_conversation_list = array_filter(
			$passed_conversation_key_list, static fn(array $el) => !in_array($el["conversation_key"], $existing_conversation_key_list, true)
		);

		// получаем ключи диалогов, в которые нужно добавить пользователя
		$to_join_conversation_key_list = array_unique(array_column($to_join_conversation_list, "conversation_key"));

		// подготовили ключи для вставки
		$to_join_conversation_list = Domain_HiringRequest_Entity_Request::doPrepareConversationKeyListToJoin($to_join_conversation_key_list, 1);
		return [$to_join_conversation_list, $existing_conversation_list];
	}

	/**
	 * пробуем получить список доступных пользователей
	 *
	 * @param array $passed_opponent_list
	 *
	 * @return array
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_AllUserKicked
	 * @throws \parseException
	 * @throws \returnException
	 * @throws Domain_User_Exception_AllAccountDeleted
	 */
	protected static function _tryGetAllowedUserList(array $passed_opponent_list):array {

		$opponent_user_id_list = array_unique(array_column($passed_opponent_list, "user_id"));

		$member_list = Gateway_Bus_CompanyCache::getMemberList($opponent_user_id_list);

		[$kicked_member_list, $account_deleted_member_list, $ok_list] = self::_sortMemberListByStatus($member_list, $opponent_user_id_list);

		if (
			Type_System_Legacy::is504ErrorThenAllUserWasKicked() && (count($opponent_user_id_list) > 0)
			&& count($opponent_user_id_list) === count($kicked_member_list)
		) {
			throw new cs_AllUserKicked();
		}

		if ((count($member_list) > 0) && count($account_deleted_member_list) == count($member_list)) {
			throw new Domain_User_Exception_AllAccountDeleted("All account was deleted");
		}

		return $ok_list;
	}

	/**
	 * Сортируем список участников по их статусу
	 *
	 * @param array $member_list
	 * @param array $opponent_user_id_list
	 *
	 * @return array[]
	 */
	protected static function _sortMemberListByStatus(array $member_list, array $opponent_user_id_list):array {

		$kicked_member_list          = [];
		$account_deleted_member_list = [];
		$ok_list                     = [];

		foreach ($member_list as $member) {

			// если пользователь уволен и об этом надо вывести ошибку
			if ($member->role === \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT) {

				$kicked_member_list[] = $member->user_id;
				continue;
			}

			if (Extra::getIsDeleted($member->extra)) {

				$account_deleted_member_list[] = $member->user_id;;
				continue;
			}

			$ok_list[] = $member->user_id;
		}

		foreach ($opponent_user_id_list as $user_id) {

			if (!array_key_exists($user_id, $member_list)) {
				$kicked_member_list[] = $user_id;
			}
		}

		return [$kicked_member_list, $account_deleted_member_list, $ok_list];
	}

	/**
	 * Возвращает список id пользователей, с которыми нужно создать диалог.
	 * @return int[]
	 */
	protected static function _getToJoinOpponentUserIdList(array $allowed_passed_opponent_user_id_list, Struct_Db_CompanyData_HiringRequest $hiring_request):array {

		[$to_join_opponent_list] = static::_resolveToJoinOpponentList($allowed_passed_opponent_user_id_list, $hiring_request);

		return array_map(
			static fn(array $el) => (int) $el["user_id"],
			$to_join_opponent_list
		);
	}

	/**
	 * мержим старый и новый списки пользователей
	 *
	 * @param array                               $allowed_passed_opponent_user_id_list
	 * @param Struct_Db_CompanyData_HiringRequest $hiring_request
	 *
	 * @return array
	 */
	protected static function _resolveToJoinOpponentList(array $allowed_passed_opponent_user_id_list, Struct_Db_CompanyData_HiringRequest $hiring_request):array {

		// существующие элементы на автоматическое вступление в чаты
		$existing_opponent_list = Domain_HiringRequest_Entity_Request::getSingleListToCreate($hiring_request->extra);

		// список ид пользователей, которые уже в списке на добавление
		$existing_opponent_user_id_list = array_column($existing_opponent_list, "user_id");

		// фильтруем только тех пользователей,
		// с которыми ранее не создавался чат через заявку
		$allowed_passed_opponent_user_id_list = array_unique(array_filter(
			$allowed_passed_opponent_user_id_list, static fn(int $el) => !in_array($el, $existing_opponent_user_id_list, true)
		));

		$to_join_opponent_list = [];

		// создаем элементы на автоматическое вступление в чаты
		// которые нужно добавить для новых собеседников
		foreach ($allowed_passed_opponent_user_id_list as $user_id) {

			$to_join_opponent_list[] = [
				"user_id" => $user_id,
				"status"  => Domain_HiringRequest_Entity_Request::HIRING_REQUEST_APPROVED_ELEMENT_STATUS,
			];
		}

		return [$to_join_opponent_list, $existing_opponent_list];
	}

	/**
	 * Обновляет данные записи для заявки на наем.
	 * Возвращает обновленную заявку.
	 */
	protected static function _updateRequestAutojoinData(Struct_Db_CompanyData_HiringRequest $hiring_request, array $passed_conversation_key_list, array $passed_opponent_list, array $allowed_passed_opponent_user_id_list):Struct_Db_CompanyData_HiringRequest {

		// формируем список из данных, но для актуальной экстры заявки
		[$to_write_conversation_key_list, $existing_conversation_key_list] = static::_resolveToJoinConversationKeyList($passed_conversation_key_list, $hiring_request);
		[$to_write_opponent_list, $existing_opponent_list] = self::_resolveToJoinOpponentList($allowed_passed_opponent_user_id_list, $hiring_request);

		// определяем значение, от которого будем считать порядок для новых элементов
		$existing_autojoin_offset = static::_getAutojoinElementCount($hiring_request);

		// добавляем упорядочивание в списки на автоматическое добавление в группы
		$ordered_conversation_list = static::_makeOrderedConversationListToWrite(
			$passed_conversation_key_list, $existing_conversation_key_list, $to_write_conversation_key_list, $existing_autojoin_offset
		);

		// добавляем упорядочивание в списки на автоматическое добавление в синглы
		$ordered_opponent_list = static::_makeOrderedOpponentListToWrite(
			$passed_opponent_list, $existing_opponent_list, $to_write_opponent_list, $existing_autojoin_offset
		);

		// обновим extra на основе данных
		$hiring_request->extra = Domain_HiringRequest_Entity_Request::setConversationKeyListToJoin($hiring_request->extra, $ordered_conversation_list);
		$hiring_request->extra = Domain_HiringRequest_Entity_Request::setSingleListToCreate($hiring_request->extra, $ordered_opponent_list);

		// пересчитываем порядок для заявки
		$hiring_request = static::_recalculateRequestAutojoinSharedOrder($hiring_request);

		try {

			// обновляем запись
			Gateway_Db_CompanyData_HiringRequest::set($hiring_request->hiring_request_id, [
				"updated_at" => time(),
				"extra"      => $hiring_request->extra,
			]);
		} catch (cs_RowNotUpdated) {
		}

		return $hiring_request;
	}

	/**
	 * Метод подготовки записи extra.
	 * Актуализирует значения порядка синглов в общем списке на добавление в чаты.
	 */
	protected static function _makeOrderedOpponentListToWrite(array $passed_opponent_list, array $existing_opponent_list, array $to_write_opponent_list, int $existing_max_order):array {

		$non_ordered_opponent_list_to_write = array_merge($existing_opponent_list, $to_write_opponent_list);
		$existing_opponent_id_list          = array_flip(array_column($existing_opponent_list, "user_id"));

		// в passed_conversation_key_list значение order приходит с клиента
		// поэтому нужно рассчитать правильное смещения с учетом уже существующих элементов

		$sorted_single = [];

		foreach ($passed_opponent_list as $single_list) {

			if (!array_key_exists($single_list["user_id"], $existing_opponent_id_list)) {
				$sorted_single[$single_list["user_id"]] = $single_list["order"] + $existing_max_order;
			}
		}

		foreach ($non_ordered_opponent_list_to_write as $key => $prepare) {

			if (array_key_exists($prepare["user_id"], $sorted_single)) {
				$non_ordered_opponent_list_to_write[$key]["order"] = $sorted_single[$prepare["user_id"]];
			}
		}

		return $non_ordered_opponent_list_to_write;
	}

	/**
	 * Актуализирует значения порядка групп в общем списке на добавление в чаты.
	 */
	protected static function _makeOrderedConversationListToWrite(array $passed_conversation_key_list, array $existing_conversation_key_list, array $to_write_conversation_key_list, int $last_element_offset):array {

		$non_ordered_conversation_list_to_write = array_merge($existing_conversation_key_list, $to_write_conversation_key_list);
		$existing_conversation_key_list         = array_flip(array_column($existing_conversation_key_list, "conversation_key"));

		// в passed_conversation_key_list значение order приходит с клиента
		// поэтому нужно рассчитать правильное смещения с учетом уже существующих элементов

		$sorted_conversation = [];

		foreach ($passed_conversation_key_list as $passed_conversation_key_item) {

			if (!array_key_exists($passed_conversation_key_item["conversation_key"], $existing_conversation_key_list)) {
				$sorted_conversation[$passed_conversation_key_item["conversation_key"]] = $passed_conversation_key_item["order"] + $last_element_offset;
			}
		}

		foreach ($non_ordered_conversation_list_to_write as $key => $prepare) {

			if (array_key_exists($prepare["conversation_key"], $sorted_conversation)) {
				$non_ordered_conversation_list_to_write[$key]["order"] = $sorted_conversation[$prepare["conversation_key"]];
			}
		}

		return $non_ordered_conversation_list_to_write;
	}

	/**
	 * Выполняем пересортировку данных, иначе на гонках значения могут разойтись.
	 */
	protected static function _recalculateRequestAutojoinSharedOrder(Struct_Db_CompanyData_HiringRequest $hiring_request):Struct_Db_CompanyData_HiringRequest {

		$to_order_conversation_list = Domain_HiringRequest_Entity_Request::getConversationKeyListToJoin($hiring_request->extra);
		$to_order_opponent_list     = Domain_HiringRequest_Entity_Request::getSingleListToCreate($hiring_request->extra);

		$conversation_index = 0;
		$opponent_index     = 0;

		$opponent_list_count = count($to_order_opponent_list);
		$index               = 1;

		usort($to_order_conversation_list, static fn(array $a, array $b) => $a["order"] > $b["order"] ? 1 : -1);
		usort($to_order_opponent_list, static fn(array $a, array $b) => $a["order"] > $b["order"] ? 1 : -1);

		// для всех диалогов бежим по циклу, здесь гарантированном разберем все диалоги
		// сингла потом отдельно нужно будет дополнить, если вдруг порядок в диалогах меньше порядка в синглах
		while ($conversation_index < count($to_order_conversation_list)) {

			if ($opponent_index === $opponent_list_count || $to_order_conversation_list[$conversation_index]["order"] < $to_order_opponent_list[$opponent_index]["order"]) {

				$to_order_conversation_list[$conversation_index]["order"] = $index++;
				$conversation_index++;
			} else {

				$to_order_opponent_list[$opponent_index]["order"] = $index++;
				$opponent_index++;
			}
		}

		// дополняем список синглов, если он не заполнен
		for ($i = $opponent_index; $i < $opponent_list_count; $i++) {
			$to_order_opponent_list[$i]["order"] = $index++;
		}

		// обновляем экстру еще раз
		$hiring_request->extra = Domain_HiringRequest_Entity_Request::setConversationKeyListToJoin($hiring_request->extra, $to_order_conversation_list);
		$hiring_request->extra = Domain_HiringRequest_Entity_Request::setSingleListToCreate($hiring_request->extra, $to_order_opponent_list);

		return $hiring_request;
	}

	/**
	 * Получим сущестовющие порядок сортировки
	 */
	protected static function _getAutojoinElementCount(Struct_Db_CompanyData_HiringRequest $hiring_request):int {

		$auto_join = $hiring_request->extra["extra"]["autojoin"];

		$max_group_order = count($auto_join["group_conversation_autojoin_item_list"]) > 0
			? max(0, ...array_column($auto_join["group_conversation_autojoin_item_list"], "order"))
			: 0;

		$max_single_order = $auto_join["single_conversation_autojoin_item_list"]
			? max(0, ...array_column($auto_join["single_conversation_autojoin_item_list"], "order"))
			: 0;

		return max($max_group_order, $max_single_order);
	}
}