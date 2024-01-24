<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\CaseException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use BaseFrame\Exception\Request\PaymentRequiredException;

/**
 * Контроллер для работы с заявками пользователей.
 */
class Apiv2_Member_Management extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getInvited",
		"confirm",
		"reject",
		"kick",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"confirm",
		"reject",
		"kick",
	];

	// методы, требующие премиум доступа
	public const ALLOW_WITH_PREMIUM_ONLY_METHODS = [];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	/**
	 * Получить список активных заявок
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 */
	public function getInvited():array {

		$offset = $this->post(\Formatter::TYPE_INT, "offset", 0);
		$limit  = $this->post(\Formatter::TYPE_INT, "limit", 100);

		try {

			$join_request_list = Domain_MemberManagement_Scenario_Api::getList(
				$this->user_id, $this->role, $this->permissions, $this->method_version, $limit + 1, $offset
			);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		return $this->ok([
			"join_request_list" => (array) array_slice($join_request_list, 0, $limit),
			"has_next"          => (int) (count($join_request_list) > $limit),
		]);
	}

	/**
	 * Подтверджаем заявку на вступление
	 *
	 * @long
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws PaymentRequiredException
	 * @throws \apiAccessException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function confirm():array {

		$join_request_id = $this->post(\Formatter::TYPE_INT, "join_request_id");
		$entry_role      = $this->post(\Formatter::TYPE_STRING, "entry_role", Domain_HiringRequest_Entity_Request::CONFIRM_ENTRY_ROLE_MEMBER);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::HIRING_REQUEST_CONFIRM);

		try {
			$join_request = Domain_MemberManagement_Scenario_Api::confirm($this->user_id, $this->role, $this->permissions, $join_request_id, $entry_role);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (cs_CompanyUserIsEmployee) {
			throw new CaseException(2209001, "User has no rights to hire");
		} catch (cs_HireRequestNotExist|cs_IncorrectHiringRequestId) {
			throw new ParamException("Hiring request doesnt exist");
		} catch (cs_HiringRequestAlreadyRejected) {
			throw new CaseException(2209004, "Request rejected");
		} catch (Domain_HiringRequest_Exception_AlreadyRevoked) {
			throw new CaseException(2209003, "Request revoked");
		} catch (Domain_MemberManagement_Exception_JoinRequestAlreadyConfirmed $e) {

			throw new CaseException(2209005, "Request already confirmed", [
				"role_name" => Member::getRoleOutputType($e->getMemberRole()),
			]);
		} catch (\cs_UserIsNotMember) {
			throw new CaseException(2209008, "Member has left the space");
		} catch (Domain_Space_Exception_ActionRestrictedByTariff) {
			throw new PaymentRequiredException(PaymentRequiredException::LIMIT_ERROR_CODE, "cant add ne members");
		} catch (Domain_HiringRequest_Exception_IncorrectEntryRole) {
			throw new ParamException("passed incorrect value of entry_role");
		}

		$this->action->users([$join_request["candidate_user_id"]]);

		return $this->ok([
			"join_request" => (object) $join_request,
		]);
	}

	/**
	 * Отклоняем заявку на вступление
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws BlockException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_HiringRequestAlreadyConfirmed
	 * @long много исключений
	 */
	public function reject():array {

		$join_request_id = $this->post(\Formatter::TYPE_INT, "join_request_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::HIRING_REQUEST_REJECT);

		try {
			$join_request = Domain_MemberManagement_Scenario_Api::reject($this->role, $this->permissions, $join_request_id);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (cs_UserHasNoRightsToHiring) {
			throw new CaseException(2209001, "User has no rights to hire");
		} catch (Domain_MemberManagement_Exception_JoinRequestAlreadyConfirmed $e) {

			throw new CaseException(2209005, "Request already confirmed", [
				"role_name" => Member::getRoleOutputType($e->getMemberRole()),
			]);
		} catch (cs_HiringRequestAlreadyRejected) {
			throw new CaseException(2209004, "Request rejected");
		} catch (Domain_HiringRequest_Exception_AlreadyRevoked) {
			throw new CaseException(2209003, "Request revoked");
		} catch (cs_HireRequestNotExist) {
			throw new ParamException("Hiring request doesnt exist");
		} catch (cs_IncorrectHiringRequestId) {
			throw new ParamException("Invalid hiring request id");
		} catch (\cs_UserIsNotMember) {
			throw new CaseException(2209008, "Member has left the space");
		}

		return $this->ok([
			"join_request" => (object) $join_request,
		]);
	}

	/**
	 * Увольняем пользователя
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function kick():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::DISMISSAL_REQUEST_CREATE_AND_APPROVE);

		try {
			Domain_MemberManagement_Scenario_Api::kick($this->user_id, $user_id, $this->method_version);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed|cs_UserHasNotRightsToDismiss) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (cs_DismissalRequestIsAlreadyExist) {
			throw new ParamException("Dismissal request already exist");
		} catch (\cs_UserIsNotMember) {
			throw new CaseException(2209006, "User is not member");
		} catch (cs_IncorrectUserId|cs_ActionNotAvailable) {
			throw new ParamException("Incorrect user_id: $user_id");
		} catch (cs_IncorrectDismissalRequestId) {
			throw new ParamException("Invalid dismissal request id");
		}

		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::DISMISSED_MEMBER);
		Type_Space_ActionAnalytics::send(COMPANY_ID, $this->user_id, Type_Space_ActionAnalytics::DISMISS_MEMBER);
		return $this->ok();
	}
}