<?php

namespace Compass\Company;

/**
 * Сценарии заявки на найм для API
 */
class Domain_HiringRequest_Scenario_Api {

	/**
	 * добавление диалог и групп к заявке
	 *
	 * @param int   $hiring_request_id
	 * @param int   $user_id
	 * @param int   $role
	 * @param int   $permissions
	 * @param array $single_list_to_create
	 * @param array $conversation_key_list_to_join
	 *
	 * @return array
	 * @throws Domain_User_Exception_AllAccountDeleted
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_AllUserKicked
	 * @throws cs_ConversationIsNotAvailableForPreset
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_HireRequestNotExist
	 * @throws cs_HiringRequestNotConfirmed
	 * @throws cs_IncorrectConversationKeyListToJoin
	 * @throws cs_IncorrectHiringRequestId
	 * @throws cs_IncorrectSingleListToCreate
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setConversationAndSingleAutoJoinList(int   $hiring_request_id, int $user_id, int $role, int $permissions,
											array $single_list_to_create, array $conversation_key_list_to_join):array {

		Domain_HiringRequest_Entity_Validator::assertCorrectListJoin($conversation_key_list_to_join, $single_list_to_create);

		$conversation_key_list_to_join = self::_doUniqueConversationKeyList($conversation_key_list_to_join);
		$single_list_to_create         = self::_doUniqueSingleList($single_list_to_create);

		Domain_HiringRequest_Entity_Validator::assertHiringRequestId($hiring_request_id);
		Domain_HiringRequest_Entity_Validator::assertConversationKeyListToJoin($conversation_key_list_to_join);
		Domain_HiringRequest_Entity_Validator::assertSingleListToCreateNew($single_list_to_create);
		Domain_HiringRequest_Entity_Validator::assertCorrectUserIdAndConversationList($conversation_key_list_to_join, $single_list_to_create);

		// получаем запись для обновления
		$hiring_request = Domain_HiringRequest_Entity_Request::get($hiring_request_id);

		// проверяем что пользователь имеет права
		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($role, $permissions);

		if ($hiring_request->status != Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED &&
			$hiring_request->status != Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED_POSTMODERATION) {

			throw new cs_HiringRequestNotConfirmed();
		}

		// проверяем, что список пользователей подходит для этой заявки
		Domain_HiringRequest_Entity_Validator::assertCorrectUserListNew($hiring_request, $single_list_to_create);

		return Domain_HiringRequest_Action_SetConversationAndSingleListAutojoin::do($user_id, $hiring_request, $conversation_key_list_to_join, $single_list_to_create);
	}

	/**
	 * Подтверждаем заявку
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 * @param int $hiring_request_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \apiAccessException
	 * @throws cs_HireRequestNotExist
	 * @throws cs_HiringRequestAlreadyConfirmed
	 * @throws cs_IncorrectHiringRequestId
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function confirm(int $user_id, int $role, int $permissions, int $hiring_request_id):array {

		Domain_HiringRequest_Entity_Validator::assertHiringRequestId($hiring_request_id);

		$hiring_request = Domain_HiringRequest_Entity_Request::get($hiring_request_id);

		// проверяем что пользователь имеет права
		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($role, $permissions);

		if ($hiring_request->status != Domain_HiringRequest_Entity_Request::STATUS_NEED_CONFIRM &&
			$hiring_request->status != Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION) {
			throw new cs_HiringRequestAlreadyConfirmed();
		}

		[$hiring_request, $user_info] = Domain_HiringRequest_Action_Confirm::do(
			$user_id, $hiring_request_id, Domain_HiringRequest_Entity_Request::CONFIRM_ENTRY_ROLE_MEMBER
		);

		[$formatted_hiring_request, $action_user_id_list] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info);

		return [$formatted_hiring_request, $action_user_id_list];
	}

	/**
	 * Отклоняем заявку
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 * @param int $hiring_request_id
	 *
	 * @return array
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_HireRequestNotExist
	 * @throws cs_HiringRequestAlreadyConfirmed
	 * @throws cs_HiringRequestAlreadyRejected
	 * @throws cs_IncorrectHiringRequestId
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserHasNoRightsToHiring
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public static function reject(int $user_id, int $role, int $permissions, int $hiring_request_id):array {

		// проверяем, параметры с запроса
		Domain_HiringRequest_Entity_Validator::assertHiringRequestId($hiring_request_id);

		// проверяем, доступы у пользователя
		try {
			\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($role, $permissions);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new cs_UserHasNoRightsToHiring();
		}

		// получаем существующую заявку
		$hiring_request = Domain_HiringRequest_Entity_Request::get($hiring_request_id);
		Domain_HiringRequest_Entity_Permission::assertHiringRequestAlreadyRejected($hiring_request);

		if ($hiring_request->status == Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION) {
			Gateway_Socket_Pivot::doRejectHiringRequest($hiring_request->hired_by_user_id, $hiring_request->candidate_user_id);
		}

		if ($hiring_request->status == Domain_HiringRequest_Entity_Request::STATUS_NEED_CONFIRM ||
			$hiring_request->status == Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED) {
			throw new cs_HiringRequestAlreadyConfirmed();
		}

		// отклоняем заявку
		$hiring_request = Domain_HiringRequest_Action_Reject::do($hiring_request);

		$user_info = Domain_HiringRequest_Action_GetUserInfoFromRequest::do($hiring_request);

		[$formatted_hiring_request] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info);

		Domain_HiringRequest_Entity_Request::sendHiringRequestStatusChangedEvent(Apiv1_Format::hiringRequest($formatted_hiring_request));

		Gateway_Event_Dispatcher::dispatch(Type_Event_HiringRequest_StatusChanged::create(Apiv1_Format::hiringRequest($formatted_hiring_request)), true);

		// форматируем группы для вступления
		$conversation_key_list_to_join  = Domain_HiringRequest_Entity_Request::getConversationKeyListToJoin($hiring_request->extra);
		$conversation_key_list_to_join  = array_column($conversation_key_list_to_join, "conversation_key");
		$filtered_group_list            = Domain_HiringRequest_Action_GetGroupConversationAutojoin::doByKeys($conversation_key_list_to_join);
		$filtered_conversation_key_list = array_column($filtered_group_list, "conversation_key");

		// получаем ключи недоступных групп
		$not_allowed_conversation_key_list = array_diff($conversation_key_list_to_join, $filtered_conversation_key_list);

		$user_info = Domain_HiringRequest_Action_GetUserInfoFromRequest::do($hiring_request);

		// форматируем ответ
		return Domain_HiringRequest_Action_Format::do($hiring_request, $user_info, $not_allowed_conversation_key_list);
	}

	/**
	 * Получаем одну заявку
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 * @param int $hiring_request_id
	 *
	 * @return array
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_HireRequestNotExist
	 * @throws cs_IncorrectHiringRequestId
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserHasNoRightsToHiring
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function get(int $user_id, int $role, int $permissions, int $hiring_request_id):array {

		// проверяем, параметры с запроса
		Domain_HiringRequest_Entity_Validator::assertHiringRequestId($hiring_request_id);

		// получаем заявку
		$hiring_request = Domain_HiringRequest_Entity_Request::get($hiring_request_id);

		// проверяем, доступы у пользователя
		if (!\CompassApp\Domain\Member\Entity\Permission::canKickMember($role, $permissions)
			&& !\CompassApp\Domain\Member\Entity\Permission::canInviteMember($role, $permissions)) {

			// если пользователь обычный, проверяем кто создавал заявку
			if ($hiring_request->hired_by_user_id != $user_id) {
				throw new cs_UserHasNoRightsToHiring();
			}
		}

		// филтруем и получаем ключей групп для вступления
		$conversation_key_list          = Domain_HiringRequest_Entity_Request::getConversationKeyListToJoin($hiring_request->extra);
		$conversation_key_list          = array_column($conversation_key_list, "conversation_key");
		$filtered_group_list            = Domain_HiringRequest_Action_GetGroupConversationAutojoin::doByKeys($conversation_key_list);
		$filtered_conversation_key_list = array_column($filtered_group_list, "conversation_key");

		$not_allowed_conversation_key_list = array_diff($conversation_key_list, $filtered_conversation_key_list);

		$user_info = Domain_HiringRequest_Action_GetUserInfoFromRequest::do($hiring_request);
		return Domain_HiringRequest_Action_Format::do($hiring_request, $user_info, $not_allowed_conversation_key_list);
	}

	/**
	 * Получаем массив заявок
	 *
	 * @param int   $user_id
	 * @param int   $role
	 * @param int   $permissions
	 * @param array $hiring_request_id_list
	 *
	 * @return array
	 * @throws cs_IncorrectHiringRequestId
	 * @throws cs_IncorrectHiringRequestIdList
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function getListById(int $user_id, int $role, int $permissions, array $hiring_request_id_list):array {

		// проверяем, параметры с запроса
		Domain_HiringRequest_Entity_Validator::assertHiringRequestIdList($hiring_request_id_list);

		// получаем массив заявок
		$hiring_request_list = Domain_HiringRequest_Entity_Request::getList($hiring_request_id_list);

		// проверяем, доступы у пользователя
		if (!\CompassApp\Domain\Member\Entity\Permission::canInviteMember($role, $permissions)
			&& !\CompassApp\Domain\Member\Entity\Permission::canKickMember($role, $permissions)) {

			// если пользователь обычный, для каждой заявки проверяем кто ее создавал
			foreach ($hiring_request_list as $k => $v) {

				// если нет доступа, убираем заявку
				if ($v->hired_by_user_id != $user_id) {
					unset($hiring_request_list[$k]);
				}
			}
		}

		// достаем ключ все диалогов полученных заявок
		$conversation_key_list = self::_getHiringConversationKeyList($hiring_request_list);

		// получаем группы для вступления этих заявок
		$join_list                      = Domain_HiringRequest_Action_GetGroupConversationAutojoin::doByKeys($conversation_key_list);
		$filtered_conversation_key_list = array_column($join_list, "conversation_key");

		// получаем ключи недоступных диалогов
		$not_allowed_conversation_key_list = array_diff($conversation_key_list, $filtered_conversation_key_list);

		$user_info_list    = [];
		$need_user_id_list = [];
		foreach ($hiring_request_list as $hiring_request) {

			if (in_array($hiring_request->status, Domain_HiringRequest_Entity_Request::ALLOW_HIRING_GET_USER_INFO_LIST)) {

				$need_user_id_list[] = $hiring_request->candidate_user_id;
				continue;
			}

			if (Domain_HiringRequest_Entity_Request::isExistCandidateUserInfo($hiring_request->extra)) {

				$user_info_list[$hiring_request->candidate_user_id] = Domain_HiringRequest_Entity_Request::getCandidateUserInfo(
					$hiring_request->extra, $hiring_request->candidate_user_id
				);
			}
		}

		// если есть что запрашивать
		if (count($need_user_id_list) > 0) {

			$temp_user_info_list = Gateway_Socket_Pivot::getUserInfoList($need_user_id_list);
			foreach ($temp_user_info_list as $user_id => $user_info) {
				$user_info_list[$user_id] = $user_info;
			}
		}

		$formatted_hiring_request_list = [];
		$action_user_id_list           = [];
		foreach ($hiring_request_list as $hiring_request) {

			$user_info = false;
			if (isset($user_info_list[$hiring_request->candidate_user_id])) {
				$user_info = $user_info_list[$hiring_request->candidate_user_id];
			}

			[$formatted_hiring_request, $temp] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info, $not_allowed_conversation_key_list);

			$formatted_hiring_request_list[] = $formatted_hiring_request;
			$action_user_id_list             = array_merge($action_user_id_list, $temp);
		}
		return [$formatted_hiring_request_list, $action_user_id_list];
	}

	/**
	 * собираем ключи диалогов всех полученных заявок
	 */
	protected static function _getHiringConversationKeyList(array $hiring_request_list):array {

		$group_conversation_key_list = [];
		foreach ($hiring_request_list as $hiring_request) {

			$conversation_key_list = Domain_HiringRequest_Entity_Request::getConversationKeyListToJoin($hiring_request->extra);
			foreach ($conversation_key_list as $v) {
				$group_conversation_key_list[$v["conversation_key"]] = true;
			}
		}

		return array_unique(array_keys($group_conversation_key_list));
	}

	/**
	 * Получаем список групповых диалогов заявки для автоподключения
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 * @param int $hiring_request_id
	 *
	 * @return array
	 * @throws cs_HireRequestNotExist
	 * @throws cs_IncorrectHiringRequestId
	 * @throws cs_UserHasNoRightsToHiring
	 * @throws \returnException
	 */
	public static function getGroupConversationAutoJoinList(int $user_id, int $role, int $permissions, int $hiring_request_id):array {

		// проверяем, параметры с запроса
		Domain_HiringRequest_Entity_Validator::assertHiringRequestId($hiring_request_id);

		// получаем заявку
		$hiring_request = Domain_HiringRequest_Entity_Request::get($hiring_request_id);

		// проверяем, доступы у пользователя
		try {
			\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($role, $permissions);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			// если пользователь обычный, проверяем кто создавал заявку
			if ($hiring_request->hired_by_user_id != $user_id) {
				throw new cs_UserHasNoRightsToHiring();
			}
		}

		return Domain_HiringRequest_Action_GetGroupConversationAutojoin::do($hiring_request);
	}

	/**
	 * Получаем массив заявок по массиву id нанимаемых пользователей
	 *
	 * @param int   $role
	 * @param int   $permissions
	 * @param array $candidate_user_id_list
	 *
	 * @return array
	 * @throws cs_IncorrectUserId
	 * @throws cs_UserHasNotRightToGetHiringHistory
	 * @throws cs_UserIdListEmpty
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function getByCandidateUserIdList(int $role, int $permissions, array $candidate_user_id_list):array {

		// проверяем, параметры с запроса
		Domain_User_Entity_Validator::assertValidUserIdList($candidate_user_id_list);

		// проверяем, доступы у пользователя
		try {
			\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($role, $permissions);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new cs_UserHasNotRightToGetHiringHistory();
		}

		// получаем массив заявок
		$hiring_request_list = Domain_HiringRequest_Entity_Request::getByCandidateUserIdList($candidate_user_id_list);

		// достаем ключ все диалогов полученных заявок
		$conversation_key_list = self::_getHiringConversationKeyList($hiring_request_list);

		// получаем группы для вступления этих заявок
		$join_list                      = Domain_HiringRequest_Action_GetGroupConversationAutojoin::doByKeys($conversation_key_list);
		$filtered_conversation_key_list = array_column($join_list, "conversation_key");

		// получаем ключи недоступных диалогов
		$not_allowed_conversation_key_list = array_diff($conversation_key_list, $filtered_conversation_key_list);

		$user_info_list    = [];
		$need_user_id_list = [];
		foreach ($hiring_request_list as $hiring_request) {

			if (in_array($hiring_request->status, Domain_HiringRequest_Entity_Request::ALLOW_HIRING_GET_USER_INFO_LIST)) {

				$need_user_id_list[] = $hiring_request->candidate_user_id;
				continue;
			}

			if (Domain_HiringRequest_Entity_Request::isExistCandidateUserInfo($hiring_request->extra)) {

				$user_info_list[$hiring_request->candidate_user_id] = Domain_HiringRequest_Entity_Request::getCandidateUserInfo(
					$hiring_request->extra, $hiring_request->candidate_user_id
				);
			}
		}

		// если есть что запрашивать
		if (count($need_user_id_list) > 0) {

			$temp_user_info_list = Gateway_Socket_Pivot::getUserInfoList($need_user_id_list);
			foreach ($temp_user_info_list as $user_id => $user_info) {
				$user_info_list[$user_id] = $user_info;
			}
		}

		$formatted_hiring_request_list = [];
		$action_user_id_list           = [];
		foreach ($hiring_request_list as $hiring_request) {

			$user_info = false;
			if (isset($user_info_list[$hiring_request->candidate_user_id])) {
				$user_info = $user_info_list[$hiring_request->candidate_user_id];
			}

			[$formatted_hiring_request, $temp] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info, $not_allowed_conversation_key_list);
			$formatted_hiring_request_list[] = $formatted_hiring_request;
			$action_user_id_list             = array_merge($action_user_id_list, $temp);
		}
		return [$formatted_hiring_request_list, $action_user_id_list];
	}

	/**
	 * Удалим дублирующиеся элементы conversation
	 *
	 * @param array $conversation_key_list_to_join
	 *
	 * @return array
	 */
	private static function _doUniqueConversationKeyList(array $conversation_key_list_to_join):array {

		$unique_list = [];

		foreach ($conversation_key_list_to_join as $conversation_key_item) {

			if (!array_key_exists($conversation_key_item["conversation_key"], $unique_list)) {

				$unique_list[$conversation_key_item["conversation_key"]] = $conversation_key_item;
			}
		}

		return $unique_list;
	}

	/**
	 * Удалим дублирующиеся элементы single
	 *
	 * @param array $single_list_to_create
	 *
	 * @return array
	 */
	private static function _doUniqueSingleList(array $single_list_to_create):array {

		$unique_list = [];

		foreach ($single_list_to_create as $single_item) {

			if (!array_key_exists($single_item["user_id"], $unique_list)) {

				$unique_list[$single_item["user_id"]] = $single_item;
			}
		}

		return $unique_list;
	}
}
