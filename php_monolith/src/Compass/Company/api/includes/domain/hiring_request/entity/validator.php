<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс для валидации данных заявки
 */
class Domain_HiringRequest_Entity_Validator {

	protected const _MAX_HIRING_REQUEST_COUNT          = 50;
	protected const _MAX_CONVERSATION_KEY_LIST_TO_JOIN = 30;
	protected const _MAX_SINGLE_LIST_TO_CREATE         = 30;
	protected const _MAX_ORDER_JOINT                   = 10000;

	// список доступных ролей, на которую можно пустить пользователя по заявке в момент одобрения заявки
	protected const _AVAILABLE_ENTRY_ROLE_LIST = [
		Domain_HiringRequest_Entity_Request::CONFIRM_ENTRY_ROLE_GUEST,
		Domain_HiringRequest_Entity_Request::CONFIRM_ENTRY_ROLE_MEMBER,
	];

	/**
	 * Выбрасываем исключение если передан некорректный entry_role
	 *
	 * @throws Domain_HiringRequest_Exception_IncorrectEntryRole
	 */
	public static function assertEntryRole(string $entry_role):void {

		if (!in_array($entry_role, self::_AVAILABLE_ENTRY_ROLE_LIST)) {
			throw new Domain_HiringRequest_Exception_IncorrectEntryRole();
		}
	}

	/**
	 * Выбрасываем исключение если передан некорректный $hiring_request_id
	 *
	 * @throws cs_IncorrectHiringRequestId
	 */
	public static function assertHiringRequestId(int $hiring_request_id):void {

		if ($hiring_request_id < 1) {
			throw new cs_IncorrectHiringRequestId();
		}
	}

	/**
	 * Выбрасываем исключение если передан некорректный $hiring_request_id_list
	 *
	 * @throws cs_IncorrectHiringRequestId
	 * @throws cs_IncorrectHiringRequestIdList
	 */
	public static function assertHiringRequestIdList(array $hiring_request_id_list):void {

		if (count($hiring_request_id_list) < 1 || count($hiring_request_id_list) > self::_MAX_HIRING_REQUEST_COUNT) {
			throw new cs_IncorrectHiringRequestIdList();
		}

		foreach ($hiring_request_id_list as $v) {
			self::assertHiringRequestId($v);
		}
	}

	/**
	 * Выбрасываем исключение если передан некорректный conversation_key_list_to_join
	 *
	 * @throws cs_IncorrectConversationKeyListToJoin
	 */
	public static function assertConversationKeyListToJoin(array $conversation_key_list_to_join):void {

		if (count($conversation_key_list_to_join) > self::_MAX_CONVERSATION_KEY_LIST_TO_JOIN) {
			throw new cs_IncorrectConversationKeyListToJoin();
		}
	}

	/**
	 * Выбрасываем исключение если передан некорректный single_list_to_create
	 *
	 * @throws cs_IncorrectSingleListToCreate
	 */
	public static function assertSingleListToCreateNew(array $single_list_to_create):void {

		if (count($single_list_to_create) > self::_MAX_SINGLE_LIST_TO_CREATE) {
			throw new cs_IncorrectSingleListToCreate();
		}

		foreach ($single_list_to_create as $user) {
			Domain_User_Entity_Validator::assertValidUserId($user["user_id"]);
		}
	}

	/**
	 * Выбрасываем исключение если передан некорректный single_list_to_create
	 *
	 * @throws cs_IncorrectSingleListToCreate
	 */
	public static function assertSingleListToCreate(array $single_list_to_create):void {

		if (count($single_list_to_create) < 1 || count($single_list_to_create) > self::_MAX_SINGLE_LIST_TO_CREATE) {
			throw new cs_IncorrectSingleListToCreate();
		}
	}

	/**
	 * Проверяет, что все переданные пользователи являются корректными, для этого набора данных.
	 *
	 * @throws paramException
	 */
	public static function assertCorrectUserList(Struct_Db_CompanyData_HiringRequest $hiring_request, array $single_list_to_create):void {

		if (in_array($hiring_request->candidate_user_id, $single_list_to_create)) {
			throw new ParamException("can't add user himself to his single list");
		}
	}

	/**
	 * Проверяет, что все переданные пользователи являются корректными, для этого набора данных.
	 *
	 * @throws paramException
	 */
	public static function assertCorrectUserListNew(Struct_Db_CompanyData_HiringRequest $hiring_request, array $single_list_to_create):void {

		foreach ($single_list_to_create as $single) {

			if ($hiring_request->candidate_user_id == $single["user_id"]) {
				throw new ParamException("can't add user himself to his single list");
			}
		}
	}

	/**
	 * Проверяет, что все переданные пользователи являются корректными
	 *
	 * @throws paramException
	 */
	public static function assertCorrectUserIdAndConversationList(array $conversation_key_list_to_join, array $single_list_to_create):void {

		if (count($conversation_key_list_to_join) == 0 && count($single_list_to_create) == 0) {
			throw new ParamException("empty param conversation key and single list");
		}
	}

	/**
	 * Проверяет, что оба массива нужной структуры
	 *
	 * @throws paramException
	 * @long
	 */
	public static function assertCorrectListJoin(array $conversation_key_list_to_join, array $single_list_to_create):void {

		foreach ($conversation_key_list_to_join as $list_item) {

			if (!isset($list_item["conversation_key"]) || !isset($list_item["order"])) {

				throw new ParamException("Not correct array conversation list");
			}

			if (strlen($list_item["conversation_key"]) < 1 || strlen($list_item["order"]) < 1) {

				throw new ParamException("Conversation key or order empty");
			}

			if (!is_numeric($list_item["order"]) || $list_item["order"] > self::_MAX_ORDER_JOINT) {

				throw new ParamException("Order not correct");
			}
		}

		foreach ($single_list_to_create as $list_item) {

			if (!isset($list_item["user_id"]) || !isset($list_item["order"])) {

				throw new ParamException("Not correct array single list");
			}

			if (strlen($list_item["user_id"]) < 1 || strlen($list_item["order"]) < 1) {

				throw new ParamException("User id or order empty");
			}

			if (!is_numeric($list_item["user_id"])) {

				throw new ParamException("User id not correct");
			}

			if (!is_numeric($list_item["order"]) || $list_item["order"] > self::_MAX_ORDER_JOINT) {

				throw new ParamException("Order not correct");
			}
		}
	}
}
