<?php

namespace Compass\Company;

use BaseFrame\Server\ServerProvider;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Сценарии заявки на увольнение для API
 */
class Domain_DismissalRequest_Scenario_Api {

	/**
	 * Добавляем заявку
	 *
	 * @param int    $creator_user_id
	 * @param int    $dismissal_user_id
	 * @param string $user_comment
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_DismissalRequestIsAlreadyExist
	 * @throws cs_IncorrectUserId
	 * @throws cs_PlatformNotFound
	 * @throws cs_Text_IsTooLong
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function add(int $creator_user_id, int $dismissal_user_id, string $user_comment):array {

		// проверяем параметры с запроса
		Domain_User_Entity_Validator::assertValidUserId($dismissal_user_id);

		// проверяем что увольняемый пользователь состоит в компании
		Domain_Member_Entity_Main::assertIsMember($dismissal_user_id);

		// преобразуем комментарий пользователя к заявке
		$user_comment = Type_Api_Filter::replaceEmojiWithShortName($user_comment);
		$user_comment = Type_Api_Filter::prepareText($user_comment, Type_Api_Filter::MAX_LENGTH_COMMENT_TEXT);

		// проверяем права пользователя создающего заявку
		try {

			$user = Domain_User_Action_Member_GetShort::do($creator_user_id);
			self::_checkUserRightToCreateDismissalRequest($user, $dismissal_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		// проверяем, что такая заявка уже есть
		if (Domain_DismissalRequest_Entity_Permission::isRequestToDismissAlreadyExist($dismissal_user_id)) {
			throw new cs_DismissalRequestIsAlreadyExist();
		}

		// создаем заявку
		[$dismissal_request, $conversation_key] = Domain_DismissalRequest_Action_Add::do($creator_user_id, $dismissal_user_id, $user_comment);

		// проверяем можем ли отдать чат
		$hiring_conversation_key = self::_checkIsAllowedConversationKey($user, $conversation_key);
		return [$dismissal_request, $hiring_conversation_key];
	}

	/**
	 * проверяем права пользователя для создания заявки
	 *
	 * @param \CompassApp\Domain\Member\Struct\Short $creator_user
	 * @param int                                   $dismissal_user_id
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_UserHasNotRightsToDismiss
	 */
	protected static function _checkUserRightToCreateDismissalRequest(\CompassApp\Domain\Member\Struct\Short $creator_user, int $dismissal_user_id):void {

		// проверяем, что пользователь пытается уволить себя
		if (Domain_DismissalRequest_Entity_Permission::isDismissingMySelf($creator_user->user_id, $dismissal_user_id)) {
			throw new cs_UserHasNotRightsToDismiss();
		}

		// проверяем, что пользователь может увольнять (все кроме простого сотрудника в общем случае)
		if (!Domain_DismissalRequest_Entity_Permission::isAllowToCreateDismissalRequest($creator_user)) {
			throw new cs_UserHasNotRightsToDismiss();
		}

		// проверяем, что можем уволить собственника
		if (!Domain_DismissalRequest_Entity_Permission::isAllowToDismissOwner($dismissal_user_id)) {
			throw new cs_UserHasNotRightsToDismiss();
		}
	}

	/**
	 * @param \CompassApp\Domain\Member\Struct\Short $user
	 * @param string                                $conversation_key
	 *
	 * @return string
	 */
	protected static function _checkIsAllowedConversationKey(\CompassApp\Domain\Member\Struct\Short $user, string $conversation_key):string {

		$is_allowed = Permission::canKickMember($user->role, $user->permissions);
		return $is_allowed ? $conversation_key : "";
	}

	/**
	 * Одобряем заявку
	 *
	 * @param int $creator_user_id
	 * @param int $dismissal_request_id
	 *
	 * @return Struct_Db_CompanyData_DismissalRequest
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_DismissalRequestAlreadyApproved
	 * @throws cs_DismissalRequestNotExist
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws cs_UserHasNotRoleToDismiss
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function approve(int $creator_user_id, int $dismissal_request_id):Struct_Db_CompanyData_DismissalRequest {

		// проверяем параметры с запроса
		Domain_DismissalRequest_Entity_Validator::assertDismissalRequestId($dismissal_request_id);

		// получаем заявку из реквеста
		$dismissal_request = Domain_DismissalRequest_Entity_Request::get($dismissal_request_id);
		Domain_DismissalRequest_Entity_Permission::assertDismissalRequestAlreadyApproved($dismissal_request);

		// проверяем права пользователя апрувящего заявку
		try {

			$user = Domain_User_Action_Member_GetShort::do($creator_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}
		self::_checkUserRightToApproveDismissalRequest($user, $dismissal_request);

		if (ServerProvider::isOnPremise()) {
			Domain_User_Entity_Validator::assertNotRootUserId($dismissal_request->dismissal_user_id);
		}

		// переведем заявку найма в статус что сотрудника уволили
		Domain_HiringRequest_Action_Dismissed::do($creator_user_id, $dismissal_request);

		// одобряем заявку
		$dismissal_request = Domain_DismissalRequest_Action_Approve::do($creator_user_id, $dismissal_request);

		// выполним все этапы увольнения
		self::createTaskDismiss($creator_user_id, $dismissal_request);

		return $dismissal_request;
	}

	/**
	 * Создаём заявку и сразу одобряем её
	 *
	 * @param int $creator_user_id
	 * @param int $dismissal_user_id
	 *
	 * @return Struct_Db_CompanyData_DismissalRequest
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_DismissalRequestIsAlreadyExist
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_IncorrectUserId
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function createAndApprove(int $creator_user_id, int $dismissal_user_id):Struct_Db_CompanyData_DismissalRequest {

		// проверяем параметры с запроса
		Domain_User_Entity_Validator::assertValidUserId($dismissal_user_id);

		// проверяем что увольняемый пользователь состоит в компании
		Domain_Member_Entity_Main::assertIsMember($dismissal_user_id);

		// проверяем права пользователя создающего заявку
		try {

			$user = Domain_User_Action_Member_GetShort::do($creator_user_id);
			self::_checkUserRightToCreateDismissalRequest($user, $dismissal_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		if (ServerProvider::isOnPremise()) {
			Domain_User_Entity_Validator::assertNotRootUserId($dismissal_user_id);
		}

		// получаем старую заявку на увольнение и подтверждаем ее, если она есть
		try {

			$old_dismissal_request = Domain_DismissalRequest_Entity_Request::getByDismissalUserId($dismissal_user_id);
			Domain_DismissalRequest_Action_Approve::do($creator_user_id, $old_dismissal_request);
		} catch (cs_DismissalRequestNotExist) {
			// нет заявки на обычное увольнение - это норма, продолжаем текущее увольнение
		}

		// создаем и одобряем заявку на увольнение
		[$dismissal_request] = Domain_DismissalRequest_Action_AddAndApprove::do($creator_user_id, $dismissal_user_id);

		// переведем заявку найма в статус что сотрудника уволили
		Domain_HiringRequest_Action_Dismissed::do($creator_user_id, $dismissal_request);

		// выполним все этапы увольнения
		self::createTaskDismiss($creator_user_id, $dismissal_request);

		return $dismissal_request;
	}

	/**
	 * Создадим задачу на увольнение
	 *
	 * @param int                                    $creator_user_id
	 * @param Struct_Db_CompanyData_DismissalRequest $dismissal_request
	 * @param string                                 $reason
	 *
	 * @throws \busException
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function createTaskDismiss(int    $creator_user_id, Struct_Db_CompanyData_DismissalRequest $dismissal_request,
							     string $reason = \CompassApp\Domain\Member\Entity\Member::LEAVE_REASON_KICKED):void {

		// проверяем параметры с запроса
		Domain_DismissalRequest_Entity_Validator::assertDismissalRequestId($dismissal_request->dismissal_request_id);

		// заблокируем доступ в компанию
		Domain_Company_Action_Dismissal::do($dismissal_request->dismissal_user_id, $reason);

		// создадим задачу увольнения
		Domain_User_Entity_TaskExit::add($dismissal_request->dismissal_user_id, $dismissal_request->dismissal_request_id);

		// создадим задачу в пивоте
		Gateway_Socket_Pivot::addScheduledCompanyTask(
			$dismissal_request->dismissal_request_id,
			Domain_User_Entity_TaskType::TYPE_EXIT,
			$creator_user_id,
		);
	}

	/**
	 * проверяем права пользователя для аппрува заявки
	 *
	 * @param \CompassApp\Domain\Member\Struct\Short  $creator_user
	 * @param Struct_Db_CompanyData_DismissalRequest $dismissal_request
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws cs_UserHasNotRoleToDismiss
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _checkUserRightToApproveDismissalRequest(\CompassApp\Domain\Member\Struct\Short $creator_user, Struct_Db_CompanyData_DismissalRequest $dismissal_request):void {

		// проверяем, что пользователь пытается уволить себя
		if (Domain_DismissalRequest_Entity_Permission::isRequestToMe($creator_user->user_id, $dismissal_request)) {
			throw new cs_UserHasNotRightsToDismiss();
		}

		// проверяем, что пользователь может увольнять (все кроме простого сотрудника в общем случае)
		if (!Domain_DismissalRequest_Entity_Permission::isAllowToApproveDismissalAndRejectRequest($creator_user)) {
			throw new cs_UserHasNotRightsToDismiss();
		}

		// проверяем, что пользователь может увольнять овнера или лидера
		if (!Domain_DismissalRequest_Entity_Permission::isAllowToApproveOrRejectDismissOwnerRequest($dismissal_request->dismissal_user_id)) {
			throw new cs_UserHasNotRoleToDismiss();
		}
	}

	/**
	 * Отклоняем заявку
	 *
	 * @param int $creator_user_id
	 * @param int $dismissal_request_id
	 *
	 * @return Struct_Db_CompanyData_DismissalRequest
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_DismissalRequestAlreadyApproved
	 * @throws cs_DismissalRequestAlreadyRejected
	 * @throws cs_DismissalRequestNotExist
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws cs_UserHasNotRoleToDismiss
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function reject(int $creator_user_id, int $dismissal_request_id):Struct_Db_CompanyData_DismissalRequest {

		// проверяем параметры с запроса
		Domain_DismissalRequest_Entity_Validator::assertDismissalRequestId($dismissal_request_id);

		// получаем заявку из реквеста
		$dismissal_request = Domain_DismissalRequest_Entity_Request::get($dismissal_request_id);
		Domain_DismissalRequest_Entity_Permission::assertDismissalRequestAlreadyApproved($dismissal_request);
		Domain_DismissalRequest_Entity_Permission::assertDismissalRequestAlreadyRejected($dismissal_request);

		// проверяем права пользователя отклонящего заявку
		try {

			$user = Domain_User_Action_Member_GetShort::do($creator_user_id);
			self::_checkUserRightToRejectDismissalRequest($user, $dismissal_request);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		// отклоняем заявку
		return Domain_DismissalRequest_Action_Reject::do($creator_user_id, $dismissal_request);
	}

	/**
	 * проверяем права пользователя для отклонения заявки
	 *
	 * @param \CompassApp\Domain\Member\Struct\Short  $creator_user
	 * @param Struct_Db_CompanyData_DismissalRequest $dismissal_request
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws cs_UserHasNotRoleToDismiss
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _checkUserRightToRejectDismissalRequest(\CompassApp\Domain\Member\Struct\Short $creator_user, Struct_Db_CompanyData_DismissalRequest $dismissal_request):void {

		// проверяем, что пользователь пытается отклонить заявку на себя
		if (Domain_DismissalRequest_Entity_Permission::isRequestToMe($creator_user->user_id, $dismissal_request)) {
			throw new cs_UserHasNotRightsToDismiss();
		}

		// проверяем, что пользователь может увольнять (все кроме простого сотрудника в общем случае)
		if (!Domain_DismissalRequest_Entity_Permission::isAllowToApproveDismissalAndRejectRequest($creator_user)) {
			throw new cs_UserHasNotRightsToDismiss();
		}

		// проверяем, что пользователь может отклонить увольнение овнера или лидера
		if (!Domain_DismissalRequest_Entity_Permission::isAllowToApproveOrRejectDismissOwnerRequest($dismissal_request->dismissal_user_id)) {
			throw new cs_UserHasNotRoleToDismiss();
		}
	}

	/**
	 * Получаем заявку
	 *
	 * @param int $creator_user_id
	 * @param int $dismissal_request_id
	 *
	 * @return Struct_Db_CompanyData_DismissalRequest
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_DismissalRequestNotExist
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws \cs_UserIsNotMember
	 */
	public static function get(int $creator_user_id, int $dismissal_request_id):Struct_Db_CompanyData_DismissalRequest {

		// проверяем параметры с запроса
		Domain_DismissalRequest_Entity_Validator::assertDismissalRequestId($dismissal_request_id);

		// получаем заявку
		$dismissal_request = Domain_DismissalRequest_Entity_Request::get($dismissal_request_id);

		// проверяем, что пользователь может прочитать заявку
		try {

			$creator_user = Domain_User_Action_Member_GetShort::do($creator_user_id);
			if (!Domain_DismissalRequest_Entity_Permission::isAllowToGetDismissalRequest($creator_user)) {
				throw new cs_UserHasNotRightsToDismiss();
			}
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		return $dismissal_request;
	}

	/**
	 * Получаем массив заявок
	 *
	 * @param int   $role
	 * @param int   $permissions
	 * @param array $dismissal_request_id_list
	 *
	 * @return array
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_IncorrectDismissalRequestIdList
	 * @throws \cs_UserIsNotMember
	 */
	public static function getList(int $role, int $permissions, array $dismissal_request_id_list):array {

		// проверяем параметры с запроса
		Domain_DismissalRequest_Entity_Validator::assertDismissalRequestIdList($dismissal_request_id_list);

		// получаем массив заявок
		$dismissal_request_list = Domain_DismissalRequest_Entity_Request::getList($dismissal_request_id_list);
		$action_user_id_list    = self::_getActionUserIdListFromDismissalRequestList($dismissal_request_list);

		// проверяем, доступы у пользователя
		if (!Permission::canKickMember($role, $permissions) && !Permission::canInviteMember($role, $permissions)) {

			foreach ($dismissal_request_list as $k => $v) {
				unset($dismissal_request_list[$k]);
			}
		}

		return [$dismissal_request_list, $action_user_id_list];
	}

	/**
	 * получаем массив id увольняемых пользователей из заявок
	 *
	 * @param array $dismissal_request_list
	 *
	 * @return array
	 */
	protected static function _getActionUserIdListFromDismissalRequestList(array $dismissal_request_list):array {

		$action_user_id_list = [];
		foreach ($dismissal_request_list as $v) {
			$action_user_id_list[] = $v->dismissal_user_id;
		}
		return $action_user_id_list;
	}
}
