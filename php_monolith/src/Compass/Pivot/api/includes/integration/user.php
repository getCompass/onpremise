<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * метод для работы интеграции с пользователями
 */
class Integration_User extends \BaseFrame\Controller\Integration {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"getUserIdByPhoneNumber",
		"getUserIdByMail",
		"setProfile",
		"doClearAvatar",
		"kickFromCompanies",
		"acceptJoinLink",
		"getUserPhoneNumberMail",
		"getUserCompanyCount",
		"setUserSpacePermissions",
		"finishOnboarding",
	];

	/**
	 * Получаем user_id пользователя по номеру телефона
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getUserIdByPhoneNumber():array {

		$phone_number = $this->post(\Formatter::TYPE_STRING, "phone_number");

		try {
			$user_id = Domain_User_Scenario_Integration::getUserIdByPhoneNumber($phone_number);
		} catch (cs_UserNotFound) {
			return $this->error(404, "user not found");
		} catch (\cs_InvalidPhoneNumber|InvalidPhoneNumber) {
			throw new ParamException("invalid phone number");
		}

		return $this->ok([
			"user_id" => (int) $user_id,
		]);
	}

	/**
	 * Получаем user_id пользователя по почте
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getUserIdByMail():array {

		$mail = $this->post(\Formatter::TYPE_STRING, "mail");

		try {
			$user_id = Domain_User_Scenario_Integration::getUserIdByMail($mail);
		} catch (cs_UserNotFound) {
			return $this->error(404, "user not found");
		} catch (InvalidMail) {
			throw new ParamException("invalid mail");
		}

		return $this->ok([
			"user_id" => (int) $user_id,
		]);
	}

	/**
	 * Обновляем информацию о пользователе
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws CompanyNotServedException
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_UnknownKeyType
	 * @long
	 */
	public function setProfile():array {

		$user_id         = $this->post(\Formatter::TYPE_INT, "user_id");
		$name            = $this->post(\Formatter::TYPE_STRING, "name", false);
		$avatar_file_key = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);
		$description     = $this->post(\Formatter::TYPE_STRING, "description", false);
		$status          = $this->post(\Formatter::TYPE_STRING, "status", false);

		$avatar_file_map = false;
		if ($avatar_file_key !== false && $avatar_file_key !== "") {
			$avatar_file_map = Type_Pack_Main::replaceKeyWithMap("file_key", $avatar_file_key);
		}

		try {
			$user_info = Domain_User_Scenario_Integration::setProfile($user_id, $name, $avatar_file_map, $description, $status);
		} catch (\cs_InvalidProfileName) {
			return $this->error(205, "invalid name");
		} catch (cs_InvalidAvatarFileMap) {
			throw new ParamException("invalid file map");
		} catch (cs_FileIsNotImage) {
			return $this->error(705, "File is not image");
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("user not found");
		} catch (Domain_User_Exception_AvatarIsDeleted) {
			throw new ParamException("avatar is deleted");
		}

		return $this->ok([
			"user" => (array) Apiv1_Pivot_Format::user($user_info),
		]);
	}

	/**
	 * Убираем аватар пользователю
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \parseException
	 * @long
	 */
	public function doClearAvatar():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Domain_User_Scenario_Integration::doClearAvatar($user_id);

		return $this->ok();
	}

	/**
	 * Удаляем пользователя из компаний
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public function kickFromCompanies():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			Domain_User_Scenario_Integration::kickFromCompanies($user_id);
		} catch (cs_CompanyNotExist|cs_CompanyIncorrectCompanyId) {
			return $this->error(1408001, "not exist company");
		} catch (cs_CompanyIsHibernate) {
			return $this->error(1408002, "company is hibernated");
		}

		return $this->ok();
	}

	/**
	 * Метод для принятия ссылки-приглашения от лица пользователя
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException
	 * @throws CompanyNotServedException
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @long
	 */
	public function acceptJoinLink():array {

		$user_id              = $this->post(\Formatter::TYPE_INT, "user_id");
		$join_link_uniq       = $this->post(\Formatter::TYPE_STRING, "join_link_uniq");
		$comment              = $this->post(\Formatter::TYPE_STRING, "comment");
		$force_postmoderation = $this->post(\Formatter::TYPE_BOOL, "force_postmoderation");

		try {
			Domain_User_Scenario_Integration::acceptJoinLink($user_id, $join_link_uniq, $comment, $force_postmoderation);
		} catch (cs_JoinLinkIsExpired|cs_IncorrectJoinLink|cs_JoinLinkNotFound|cs_JoinLinkIsNotActive|cs_JoinLinkIsUsed) {
			return $this->error(1199, "invite-link not active");
		} catch (cs_CompanyNotExist) {
			return $this->error(1102, "Company not found");
		} catch (cs_RowDuplication) {
			throw new ParamException("Passed incorrect params");
		} catch (cs_Text_IsTooLong) {
			throw new ParamException("User comment is too long");
		} catch (cs_UserAlreadyInCompany $e) {

			return $this->error(1203, "member is already in company", [
				"company_id"      => $e->getCompanyId(),
				"inviter_user_id" => $e->getInviterUserId(),
			]);
		} catch (cs_ExitTaskInProgress) {
			return $this->error(1220, "user has not finished exit the company yet");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("invalid company id");
		} catch (Domain_Company_Exception_IsRelocating) {
			throw new \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException("company is relocating");
		} catch (Domain_Company_Exception_IsHibernated) {
			throw new \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException("company is hibernated");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new CompanyNotServedException("company is not served");
		} catch (Domain_Link_Exception_UserNotFinishRegistration) {
			throw new CaseException(1211001, "user not finish registration");
		}

		return $this->ok();
	}

	/**
	 * получаем номер телефона, почту пользователя
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getUserPhoneNumberMail():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		[$phone_number, $mail] = Domain_User_Scenario_Integration::getUserPhoneNumberMail($user_id);

		return $this->ok([
			"phone_number" => (string) $phone_number,
			"mail"         => (string) $mail,
		]);
	}

	/**
	 * получаем количество компаний пользователя
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getUserCompanyCount():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		$company_count = Domain_User_Scenario_Integration::getUserCompanyCount($user_id);

		return $this->ok([
			"company_count" => (int) $company_count,
		]);
	}

	/**
	 * устанавливаем права участнику пространства
	 *
	 * @return array
	 * @throws CompanyNotServedException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_CompanyIsNotActive
	 */
	public function setUserSpacePermissions():array {

		$user_id     = $this->post(\Formatter::TYPE_INT, "user_id");
		$space_id    = $this->post(\Formatter::TYPE_INT, "space_id");
		$permissions = $this->post(\Formatter::TYPE_JSON, "permissions", []);

		try {
			Domain_User_Scenario_Integration::setUserSpacePermissions($user_id, $space_id, $permissions);
		} catch (cs_UserNotFound) {
			throw new ParamException("user is not member of company");
		}

		return $this->ok();
	}

	/**
	 * завершаем обнординг пользователю
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws CaseException
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 */
	public function finishOnboarding():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$type    = $this->post(\Formatter::TYPE_STRING, "type");

		try {
			Domain_User_Scenario_Api::finishOnboarding($user_id, $type);
		} catch (Domain_User_Exception_Onboarding_NotAllowedStatusStep) {
			throw new CaseException(1208003, "onboarding cant be finished");
		} catch (Domain_User_Exception_Onboarding_NotAllowedType) {
			throw new ParamException("invalid type");
		}

		return $this->ok();
	}
}
