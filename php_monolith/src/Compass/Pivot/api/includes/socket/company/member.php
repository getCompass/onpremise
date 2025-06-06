<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для работы с участниками компании
 */
class Socket_Company_Member extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"isAlreadyInCompany",
		"doRejectHiringRequest",
		"kick",
		"getCreatorUserId",
		"doConfirmHiringRequest",
		"deleteInvitesByLink",
		"getListActiveMember",
		"onUpgradeGuest",
		"isMediaConferenceCreatingAllowed",
		"checkIsAllowedForCall",
	];

	/**
	 * Проверяем находится ли пользователь уже в компании
	 *
	 * @return array
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function isAlreadyInCompany():array {

		$candidate_phone = $this->post(\Formatter::TYPE_STRING, "candidate_phone");

		try {

			$user_id = Domain_User_Entity_Phone::getUserIdByPhone($candidate_phone);
			Domain_Company_Entity_User_Member::assertUserIsNotMemberOfCompany($user_id, $this->company_id);
		} catch (cs_PhoneNumberNotFound) {

			return $this->ok([
				"user_id" => (int) 0,
			]);
		} catch (cs_UserAlreadyInCompany $e) {

			return $this->ok([
				"user_id" => (int) $e->getUserId(),
			]);
		}

		return $this->ok([
			"user_id" => (int) 0,
		]);
	}

	/**
	 * Получить id создателя компании
	 */
	public function getCreatorUserId():array {

		$company = Domain_Company_Entity_Company::get($this->company_id);

		return $this->ok([
			"creator_user_id" => (int) $company->created_by_user_id,
		]);
	}

	/**
	 * Исключить пользователя из компании
	 *
	 * @return array
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException|\paramException
	 */
	public function kick():array {

		$need_add_user_lobby = $this->post(\Formatter::TYPE_BOOL, "need_add_user_lobby");
		$reason              = $this->post(\Formatter::TYPE_STRING, "reason");
		$role                = $this->post(\Formatter::TYPE_INT, "role");

		$is_approved = Domain_Company_Scenario_Socket::kickMember($this->company_id, $this->user_id, $role, $need_add_user_lobby, $reason);

		return $this->ok([
			"is_approved" => (int) $is_approved,
		]);
	}

	/**
	 * активировать пользователя
	 *
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_CompanyUserIsNotFound
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserNotFound
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function doConfirmHiringRequest():array {

		$user_company_token     = $this->post(\Formatter::TYPE_STRING, "user_company_token");
		$inviter_user_id        = $this->post(\Formatter::TYPE_INT, "inviter_user_id");
		$approved_by_user_id    = $this->post(\Formatter::TYPE_INT, "approved_by_user_id");
		$user_space_role        = $this->post(\Formatter::TYPE_INT, "user_space_role");
		$user_space_permissions = $this->post(\Formatter::TYPE_INT, "user_space_permissions");

		Domain_Company_Scenario_Socket::doConfirmHiringRequest(
			$this->user_id, $this->company_id, $user_space_role, $user_space_permissions, $user_company_token, $inviter_user_id, $approved_by_user_id
		);

		return $this->ok();
	}

	/**
	 * Отзываем инвайт в компанию
	 *
	 * @return array
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public function doRejectHiringRequest():array {

		$inviter_user_id = $this->post(\Formatter::TYPE_INT, "inviter_user_id");

		Domain_Company_Scenario_Socket::doRejectHiringRequest($this->user_id, $inviter_user_id, $this->company_id);

		return $this->ok();
	}

	/**
	 * При переводе гостя в роль участника пространства
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public function onUpgradeGuest():array {

		Domain_Company_Scenario_Socket::onUpgradeGuest($this->company_id);

		return $this->ok();
	}

	/**
	 * Проверяет что указанный пользователь может начать видеоконференцию в указанном пространстве
	 *
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	public function isMediaConferenceCreatingAllowed():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		[$is_allowed, $error_code] = Domain_Company_Scenario_Socket::isMediaConferenceCreatingAllowed($user_id, $company_id);

		return $this->ok(["is_allowed" => (int) $is_allowed, "error_code" => (int) $error_code]);
	}

	/**
	 * Проверяет что указанный пользователь может начать видеоконференцию в указанном пространстве
	 *
	 * @return array
	 * @throws CompanyNotServedException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	public function checkIsAllowedForCall():array {

		$user_id          = $this->post(\Formatter::TYPE_INT, "user_id");
		$opponent_user_id = $this->post(\Formatter::TYPE_INT, "opponent_user_id");
		$space_id         = $this->post(\Formatter::TYPE_INT, "space_id");

		try {
			$conversation_map = Domain_Jitsi_Scenario_Socket::checkIsAllowedForCall($user_id, $opponent_user_id, $space_id);
		} catch (Domain_Jitsi_Exception_GuestIsInitiator) {
			return $this->error(2419011, "user is guest");
		} catch (Domain_Jitsi_Exception_IsNotAllowed) {
			return $this->error(2419010, "user is not allowed to create call");
		} catch (Domain_Company_Exception_NotExist) {
			throw new ParamException("invalid space");
		}

		return $this->ok([
			"conversation_map" => (string) $conversation_map,
		]);
	}
}