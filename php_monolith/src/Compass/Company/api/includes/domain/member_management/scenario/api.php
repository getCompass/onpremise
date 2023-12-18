<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Сценарии заявок участников компании для API
 */
class Domain_MemberManagement_Scenario_Api {

	/**
	 * Получить список участников компании
	 *
	 * @long
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function getList(int $user_id, int $role, int $permissions, int $method_version, int $limit, int $offset):array {

		if ($limit < 1 || $offset < 0) {
			throw new ParamException("invalid limit or offset");
		}

		// проверяем права пользователя
		Domain_Member_Entity_Permission::checkSpace($user_id, $method_version, Permission::IS_SHOW_COMPANY_MEMBER_ENABLED);
		Permission::assertCanInviteMember($role, $permissions);

		// получаем активные заявки
		$request_list = Domain_HiringRequest_Entity_Request::getNeedPostmoderationList($limit, $offset);

		// получаем информацию по кандидатам для вступления в компанию
		$need_user_id_list = [];
		foreach ($request_list as $request) {
			$need_user_id_list[] = $request->candidate_user_id;
		}

		// если есть кого запрашивать
		if (count($need_user_id_list) > 0) {

			$temp_user_info_list = Gateway_Socket_Pivot::getUserInfoList($need_user_id_list);
			foreach ($temp_user_info_list as $user_id => $user_info) {
				$user_info_list[$user_id] = $user_info;
			}
		}

		// приводим к формату заявку
		$formatted_request_list = [];
		foreach ($request_list as $request) {

			$data = [
				"invited_comment" => Domain_HiringRequest_Entity_Request::getComment($request->extra),
			];
			if (isset($user_info_list[$request->candidate_user_id])) {

				$data["candidate_user_info"] = [
					"full_name"       => $user_info_list[$request->candidate_user_id]->full_name,
					"avatar_file_key" => $user_info_list[$request->candidate_user_id]->avatar_file_key,
					"avatar_color_id" => $user_info_list[$request->candidate_user_id]->avatar_color_id,
				];
			}

			$formatted_request_list[] = Apiv2_Format::joinRequest($request, $data);
		}

		return $formatted_request_list;
	}

	/**
	 * Подтверждаем заявку на вступление
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \apiAccessException
	 * @throws cs_HireRequestNotExist
	 * @throws Domain_MemberManagement_Exception_JoinRequestAlreadyConfirmed
	 * @throws cs_HiringRequestAlreadyRejected
	 * @throws cs_IncorrectHiringRequestId
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws ParamException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 */
	public static function confirm(int $user_id, int $role, int $permissions, int $join_request_id, string $entry_role):array {

		Domain_HiringRequest_Entity_Validator::assertEntryRole($entry_role);
		Domain_HiringRequest_Entity_Validator::assertHiringRequestId($join_request_id);
		$join_request = Domain_HiringRequest_Entity_Request::get($join_request_id);
		Permission::assertCanInviteMember($role, $permissions);

		if ($join_request->status == Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED_POSTMODERATION ||
			$join_request->status == Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED) {

			self::_throwJoinRequestAlreadyConfirmed($join_request->candidate_user_id);
		}

		Domain_HiringRequest_Entity_Permission::assertHiringRequestAlreadyRejected($join_request);
		Domain_HiringRequest_Entity_Permission::assertHiringRequestAlreadyRevoked($join_request);

		if ($join_request->status == Domain_HiringRequest_Entity_Request::STATUS_DISMISSED) {
			throw new \cs_UserIsNotMember();
		}

		if ($join_request->status != Domain_HiringRequest_Entity_Request::STATUS_NEED_CONFIRM &&
			$join_request->status != Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION) {

			throw new ParamException("incorrect status for hiring: {$join_request->status}");
		}

		/** @var Struct_Db_CompanyData_HiringRequest $join_request */
		[$join_request, $user_info] = Domain_HiringRequest_Action_Confirm::do($user_id, $join_request_id, $entry_role);

		$data["invited_comment"]     = Domain_HiringRequest_Entity_Request::getComment($join_request->extra);
		$data["candidate_user_info"] = [
			"full_name"       => $user_info->full_name,
			"avatar_file_key" => $user_info->avatar_file_key,
			"avatar_color_id" => $user_info->avatar_color_id,
		];

		return Apiv2_Format::joinRequest($join_request, $data);
	}

	/**
	 * Отклоняем заявку на вступление
	 *
	 * @long
	 * @throws Domain_HiringRequest_Exception_AlreadyRevoked
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_HireRequestNotExist
	 * @throws cs_HiringRequestAlreadyConfirmed
	 * @throws cs_HiringRequestAlreadyRejected
	 * @throws cs_IncorrectHiringRequestId
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function reject(int $role, int $permissions, int $join_request_id):array {

		// проверяем, параметры с запроса
		Domain_HiringRequest_Entity_Validator::assertHiringRequestId($join_request_id);

		Permission::assertCanInviteMember($role, $permissions);

		// получаем существующую заявку
		$join_request = Domain_HiringRequest_Entity_Request::get($join_request_id);
		Domain_HiringRequest_Entity_Permission::assertHiringRequestAlreadyRejected($join_request);
		Domain_HiringRequest_Entity_Permission::assertHiringRequestAlreadyRevoked($join_request);

		// проверяем статусы заявки на найм
		try {
			self::_checkHiringRequestStatus($join_request);
		} catch (cs_HiringRequestAlreadyConfirmed) {

			// отлавливаем старое исключение и выбрасываем то, что поновее
			self::_throwJoinRequestAlreadyConfirmed($join_request->candidate_user_id);
		}

		// отклоняем заявку
		$join_request = Domain_HiringRequest_Action_Reject::do($join_request);

		$user_info = Domain_HiringRequest_Action_GetUserInfoFromRequest::do($join_request);

		// для старых клиентов отправляем событие со старым форматом заявки
		[$formatted_hiring_request] = Domain_HiringRequest_Action_Format::do($join_request, $user_info);
		Domain_HiringRequest_Entity_Request::sendHiringRequestStatusChangedEvent(Apiv1_Format::hiringRequest($formatted_hiring_request));

		$data["invited_comment"]     = Domain_HiringRequest_Entity_Request::getComment($join_request->extra);
		$data["candidate_user_info"] = [
			"full_name"       => $user_info->full_name,
			"avatar_file_key" => $user_info->avatar_file_key,
			"avatar_color_id" => $user_info->avatar_color_id,
		];

		return Apiv2_Format::joinRequest($join_request, $data);
	}

	/**
	 * Проверяем статусы заявки на найм
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_HiringRequestAlreadyConfirmed
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _checkHiringRequestStatus(Struct_Db_CompanyData_HiringRequest $join_request):void {

		if ($join_request->status == Domain_HiringRequest_Entity_Request::STATUS_DISMISSED) {
			throw new \cs_UserIsNotMember();
		}

		if ($join_request->status == Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION) {
			Gateway_Socket_Pivot::doRejectHiringRequest($join_request->hired_by_user_id, $join_request->candidate_user_id);
		}

		if ($join_request->status == Domain_HiringRequest_Entity_Request::STATUS_NEED_CONFIRM ||
			$join_request->status == Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED ||
			$join_request->status == Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED_POSTMODERATION) {

			throw new cs_HiringRequestAlreadyConfirmed();
		}
	}

	/**
	 * Увольняем пользователя
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
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
	 * @long
	 */
	public static function kick(int $creator_user_id, int $dismissal_user_id, int $method_version):void {

		// проверяем параметры с запроса
		Domain_User_Entity_Validator::assertValidUserId($dismissal_user_id);

		// проверяем, что увольняемый пользователь состоит в компании
		Domain_Member_Entity_Main::assertIsMember($dismissal_user_id);

		// проверяем права пользователей
		try {

			$user = Domain_User_Action_Member_GetShort::do($creator_user_id);
			self::_checkUserRightToCreateDismissalRequest($user, $dismissal_user_id);
			Domain_Member_Entity_Permission::checkSpace($creator_user_id, $method_version, Permission::IS_SET_MEMBER_PROFILE_ENABLED);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		if (ServerProvider::isOnPremise()) {
			Domain_User_Entity_Validator::assertNotRootUserId($dismissal_user_id);
		}

		// получаем старую заявку на увольнение и подтверждаем её, если она есть
		try {

			$old_dismissal_request = Domain_DismissalRequest_Entity_Request::getByDismissalUserId($dismissal_user_id);
			Domain_DismissalRequest_Action_Approve::do($creator_user_id, $old_dismissal_request);
		} catch (cs_DismissalRequestNotExist) {
			// нет заявки на обычное увольнение - это норма, продолжаем текущее увольнение
		}

		// создаём и одобряем заявку на увольнение
		[$dismissal_request] = Domain_DismissalRequest_Action_AddAndApprove::do($creator_user_id, $dismissal_user_id);

		// переведём заявку найма в статус, что сотрудника уволили
		Domain_HiringRequest_Action_Dismissed::do($creator_user_id, $dismissal_request);

		// проверяем параметры с запроса
		Domain_DismissalRequest_Entity_Validator::assertDismissalRequestId($dismissal_request->dismissal_request_id);

		// заблокируем доступ в компанию
		Domain_Company_Action_Dismissal::do($dismissal_request->dismissal_user_id, administrator_user_id: $creator_user_id);

		// создадим задачу увольнения
		Domain_User_Entity_TaskExit::add($dismissal_request->dismissal_user_id, $dismissal_request->dismissal_request_id);

		// создадим задачу в пивоте
		Gateway_Socket_Pivot::addScheduledCompanyTask(
			$dismissal_request->dismissal_request_id,
			Domain_User_Entity_TaskType::TYPE_EXIT,
			$creator_user_id,
		);

		// отмечаем в intercom, что пользователь покинул пространство
		$config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::MEMBER_COUNT);
		Gateway_Socket_Intercom::userLeaved($dismissal_user_id, $config["value"] ?? 0);
	}

	/**
	 * проверяем права пользователя для создания заявки
	 *
	 * @param \CompassApp\Domain\Member\Struct\Short $creator_user
	 * @param int                                    $dismissal_user_id
	 *
	 * @throws \busException
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws \parseException
	 * @throws \returnException
	 * @throws ParamException
	 * @throws \apiAccessException
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
		if (!Domain_DismissalRequest_Entity_Permission::isAllowToDismissOwner($dismissal_user_id, false)) {
			throw new cs_UserHasNotRightsToDismiss();
		}
	}

	/**
	 * Получаем все необходимые данные и выбрасываем исключение что заявка на вступление в пространство уже приянта
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	protected static function _throwJoinRequestAlreadyConfirmed(int $candidate_user_id):void {

		// роль участника
		$member_role = \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT;

		// получаем информацию об участнике, чью заявку одобрили
		try {

			$member_info = Gateway_Bus_CompanyCache::getMember($candidate_user_id);
			$member_role = $member_info->role;
		} catch (\cs_RowIsEmpty) {
		}

		throw new Domain_MemberManagement_Exception_JoinRequestAlreadyConfirmed($member_role);
	}
}
