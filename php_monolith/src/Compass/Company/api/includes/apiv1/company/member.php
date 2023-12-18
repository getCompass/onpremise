<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Контроллер для работы с пользователями.
 */
class Apiv1_Company_Member extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getUserRoleList",
		"getStatusUserList",
		"leave",
		"setDescription",
		"setStatus",
		"setBadge",
		"setMBTIType",
		"setJoinTime",
		"setDescriptionBadgeAndJoinTime",
		"getBatchingList",
		"getListByMBTI",
		"setProfile",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"leave",
		"setDescription",
		"setStatus",
		"setBadge",
		"setMBTIType",
		"setJoinTime",
		"setDescriptionBadgeAndJoinTime",
		"setProfile",
	];

	// методы, требующие премиум доступа
	public const ALLOW_WITH_PREMIUM_ONLY_METHODS = [

	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => [
			"setDescription",
			"setBadge",
			"setMBTIType",
			"setJoinTime",
			"getListByMBTI",
		],
	];

	/**
	 * Метод для получения списка id пользователей с ролями
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getUserRoleList():array {

		$roles = $this->post(\Formatter::TYPE_ARRAY_INT, "roles");

		$member_list = Domain_User_Scenario_Api::getUserRoleList($roles);

		$this->action->users(array_keys($member_list));
		return $this->ok([
			"user_list" => (object) Apiv1_Format::memberRoleList($member_list, true),
		]);
	}

	/**
	 * Отдаем запрошенных пользователей, сгруппированными по статусу в системе
	 *
	 * @post           user_list: int[]
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getStatusUserList():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_list");

		try {
			[$disabled_user_id_list, $account_deleted_user_id_list, $allowed_user_id_list] = Domain_User_Scenario_Api::getUserIdListWithStatusInSystem($this->user_id, $user_id_list);
		} catch (cs_IncorrectUserId) {
			throw new ParamException("Incorrect user_id in list");
		} catch (cs_UserIdListEmpty) {
			throw new ParamException("param user_id_list is empty");
		}

		return $this->ok([
			"disabled_user_id_list"        => (array) $disabled_user_id_list,
			"account_deleted_user_id_list" => (array) $account_deleted_user_id_list,
			"allowed_user_id_list"         => (array) $allowed_user_id_list,
		]);
	}

	/**
	 * Метод обновления описания
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 */
	public function setDescription():array {

		$description = $this->post(\Formatter::TYPE_STRING, "description");
		$user_id     = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETDESCRIPTION);

		try {
			Domain_Member_Scenario_Api::setDescription($this->user_id, $this->role, $this->permissions, $user_id, $description, $this->method_version);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			return match ($this->method_version) {

				1       => $this->error(655, "not access for action"),
				default => $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "not access for action")
			};
		} catch (cs_IncorrectUserId) {
			throw new ParamException("incorect user id");
		} catch (\cs_UserIsNotMember) {
			return $this->error(532, "this user left company");
		} catch (Domain_User_Exception_IsAccountDeleted) {
			return $this->error(2106001, "User delete his account");
		} catch (\CompassApp\Domain\Member\Exception\ActionRestrictForUser) {
			return $this->error(2106002, "Action restrict for user");
		} catch (\CompassApp\Domain\Member\Exception\UserIsGuest) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "not access for action");
		}

		return $this->ok();
	}

	/**
	 * Метод обновления статуса
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function setStatus():array {

		$status  = $this->post(\Formatter::TYPE_STRING, "status");
		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETSTATUS);

		try {

			Domain_Member_Entity_Permission::checkSpace($this->user_id, $this->method_version, Permission::IS_SET_MEMBER_PROFILE_ENABLED);
			Domain_Member_Scenario_Api::setStatus($this->user_id, $this->method_version,
				$this->role, $this->permissions, $user_id, $status);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			return match ($this->method_version) {

				1       => $this->error(655, "not access for action"),
				default => $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "not access for action")
			};
		} catch (cs_IncorrectUserId) {
			throw new ParamException("incorrect user id");
		} catch (\CompassApp\Domain\Member\Exception\ActionRestrictForUser) {
			return $this->error(2106004, "Action restrict for user");
		}

		return $this->ok();
	}

	/**
	 * Метод для установки типа личности
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public function setMBTIType():array {

		$mbti_type = $this->post(\Formatter::TYPE_STRING, "mbti_type");
		$user_id   = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETMBTITYPE);

		try {
			Domain_Member_Scenario_Api::setMBTIType($this->user_id, $this->role, $this->permissions, $user_id, $mbti_type);
		} catch (cs_InvalidProfileMbti) {
			throw new ParamException("invalid mbti type");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			return $this->error(655, "not access for action");
		} catch (cs_IncorrectUserId) {
			throw new ParamException("incorrect user id");
		} catch (\cs_UserIsNotMember) {
			return $this->error(532, "this user left company");
		} catch (Domain_User_Exception_IsAccountDeleted) {
			return $this->error(2106001, "User delete his account");
		}

		return $this->ok();
	}

	/**
	 * Метод для установки бейджа
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function setBadge():array {

		$content  = $this->post(\Formatter::TYPE_STRING, "content", false);
		$color_id = $this->post(\Formatter::TYPE_INT, "color_id", false);
		$user_id  = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETBADGE);

		try {
			Domain_Member_Scenario_Api::setBadge($this->user_id, $this->role, $this->permissions, $user_id, $color_id, $content, $this->method_version);
		} catch (cs_InvalidProfileBadge) {
			throw new ParamException("invalid badge");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			return match ($this->method_version) {

				1       => $this->error(655, "not access for action"),
				default => $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "not access for action")
			};
		} catch (cs_IncorrectUserId) {
			throw new ParamException("Incorrect user id");
		} catch (\cs_UserIsNotMember) {
			return $this->error(532, "this user left company");
		} catch (Domain_User_Exception_IsAccountDeleted) {
			return $this->error(2106001, "User delete his account");
		} catch (\CompassApp\Domain\Member\Exception\ActionRestrictForUser) {
			return $this->error(2106003, "Action restrict for user");
		} catch (\CompassApp\Domain\Member\Exception\UserIsGuest) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "not access for action");
		}

		return $this->ok();
	}

	/**
	 * Метод для установки времени вступления в компанию
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \busException
	 * @throws cs_DatesWrongOrder
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public function setJoinTime():array {

		$time    = $this->post(\Formatter::TYPE_INT, "time");
		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETJOINTIME);

		try {
			$total_worked_time = Domain_Member_Scenario_Api::setJoinTime($this->role, $this->permissions, $user_id, $time);
		} catch (cs_InvalidProfileJoinTime) {
			throw new ParamException("invalid time");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			return $this->error(655, "not access for action");
		} catch (cs_IncorrectUserId) {
			throw new ParamException("Incorrect user id");
		} catch (\cs_UserIsNotMember) {
			return $this->error(532, "this user left company");
		} catch (Domain_User_Exception_IsAccountDeleted) {
			return $this->error(2106001, "User delete his account");
		}

		return $this->ok([
			"total_worked_time" => (float) $total_worked_time,
		]);
	}

	/**
	 * метод для обновления описания бейджа и времени вступления
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \busException
	 * @throws cs_InvalidProfileBadge
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function setDescriptionBadgeAndJoinTime():array {

		$description = $this->post(\Formatter::TYPE_STRING, "description", false);
		$content     = $this->post(\Formatter::TYPE_STRING, "content", false);
		$color_id    = $this->post(\Formatter::TYPE_INT, "color_id", false);
		$time        = $this->post(\Formatter::TYPE_INT, "time", false);
		$user_id     = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETDESCRIPTION);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETBADGE);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETJOINTIME);

		try {
			Domain_Member_Scenario_Api::setDescriptionBadgeAndJoinTime($this->role, $this->permissions, $user_id, $description, $time, $color_id, $content);
		} catch (cs_InvalidProfileJoinTime) {
			throw new ParamException("invalid time");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			return $this->error(655, "not access for action");
		} catch (cs_IncorrectUserId) {
			throw new ParamException("Incorrect user id");
		} catch (\cs_UserIsNotMember) {
			return $this->error(532, "this user left company");
		} catch (Domain_User_Exception_IsAccountDeleted) {
			return $this->error(2106001, "User delete his account");
		} catch (\CompassApp\Domain\Member\Exception\UserIsGuest) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "not access for action");
		}

		return $this->ok();
	}

	/**
	 * Метод для получения пользователей
	 *
	 * @return array
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getBatchingList():array {

		$need_user_id_list = $this->post(\Formatter::TYPE_JSON, "need_user_id_list", []);
		$batch_user_list   = $this->post(\Formatter::TYPE_JSON, "batch_user_list");

		try {
			[$member_list, $left_user_id_list] = Domain_Member_Scenario_Api::getBatchingList($batch_user_list, $need_user_id_list);
		} catch (cs_WrongSignature) {
			throw new ParamException("incorrect batch_user_list");
		} catch (cs_IncorrectUserId) {
			throw new ParamException("incorrect need_user_id_list");
		}

		return $this->ok([
			"member_list"       => (array) Apiv1_Format::memberList($member_list),
			"left_user_id_list" => (array) Apiv1_Format::leftUserIdList($left_user_id_list),
		]);
	}

	/**
	 * Метод для получения списка пользователей с идентичным типом личности
	 *
	 * @return array
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getListByMBTI():array {

		$mbti_type = $this->post(\Formatter::TYPE_STRING, "mbti_type");
		$offset    = $this->post(\Formatter::TYPE_INT, "offset", 0);
		$count     = $this->post(\Formatter::TYPE_INT, "count", 0);

		try {
			[$user_id_list, $has_next] = Domain_Member_Scenario_Api::getListByMBTI($mbti_type, $offset, $count);
		} catch (cs_InvalidProfileMbti) {
			throw new ParamException("invalid mbti type");
		}

		$this->action->users($user_id_list);

		return $this->ok([
			"user_list" => (array) $user_id_list,
			"has_next"  => (int) $has_next,
		]);
	}

	/**
	 * Покидаем компанию (самоувольнение)
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \apiAccessException
	 * @throws \blockException
	 * @throws \busException
	 * @throws cs_AnswerCommand
	 * @throws \cs_CompanyUserIsNotOwner
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_PlatformNotFound
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function leave():array {

		$two_fa_key = $this->post(\Formatter::TYPE_STRING, "two_fa_key", false);

		try {
			Domain_User_Scenario_Api::leaveCompany($this->user_id, $this->role, $two_fa_key, $this->method_version);
		} catch (\cs_UserIsNotMember) {
			return $this->error(1002, "you are not a company member");
		} catch (cs_ActionForCompanyBlocked) {

			Gateway_Bus_CollectorAgent::init()->inc("row29");
			return $this->error(1005, "action for this company temporary blocked");
		} catch (cs_CompanyUserIsOnlyOwner) {
			return $this->error(1012, "user is the only owner in the company");
		} catch (cs_TwoFaIsInvalid) {
			return $this->error(2302, "2fa key is not valid");
		} catch (cs_TwoFaIsNotActive) {
			return $this->error(2303, "2fa key is not active. You need to confirm phone number");
		}

		return $this->ok();
	}

	/**
	 * метод для обновления описания бейджа и времени вступления
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function setProfile():array {

		$description    = $this->post(\Formatter::TYPE_STRING, "description", false);
		$badge_content  = $this->post(\Formatter::TYPE_STRING, "badge_content", false);
		$badge_color_id = $this->post(\Formatter::TYPE_INT, "badge_color_id", false);
		$status         = $this->post(\Formatter::TYPE_STRING, "status", false);
		$user_id        = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETPROFILE);

		try {
			Domain_Member_Scenario_Api::setProfile($this->user_id, $this->role, $this->permissions, $user_id, $description, $status, $badge_color_id, $badge_content);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			return $this->error(655, "not access for action");
		} catch (cs_InvalidProfileBadge) {
			throw new ParamException("invalid badge");
		} catch (cs_IncorrectUserId) {
			throw new ParamException("Incorrect user id");
		} catch (\cs_UserIsNotMember) {
			return $this->error(532, "this user left company");
		} catch (Domain_Member_Exception_SetProfileRestrictForUser $e) {

			return $this->error(2106006, "There is action restrict for user ", [
				"updated"    => $e->getOutput()["updated"],
				"restricted" => $e->getOutput()["restricted"],
			]);
		} catch (\CompassApp\Domain\Member\Exception\UserIsGuest) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "not access for action");
		}

		return $this->ok();
	}
}