<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;
use BaseFrame\Exception\DomainException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;

/**
 * контроллер для методов авторизации api/v2/auth/...
 */
class Apiv2_Auth extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"sendAttributionOfRegistration",
		"tryAuthenticationToken",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод для фиксации параметров, с которыми зарегистрировался пользователь
	 * @long
	 */
	public function sendAttributionOfRegistration():array {

		$timezone_utc_offset = $this->post(\Formatter::TYPE_INT, "timezone_utc_offset");
		$screen_avail_width  = $this->post(\Formatter::TYPE_INT, "screen_avail_width");
		$screen_avail_height = $this->post(\Formatter::TYPE_INT, "screen_avail_height");

		// первым делом проверяем, что все параметры присланы правильно
		Domain_User_Scenario_Api_AuthV2::assertAttributionParameters($timezone_utc_offset, $screen_avail_width, $screen_avail_height);

		try {

			// даем использовать метод только 1 раз в год
			// таким образом защищаемся, чтобы не флудили методом
			// супер дешевое решение
			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::AUTH_ON_REGISTRATION);

			$output = Domain_User_Scenario_Api_AuthV2::sendAttributionOfRegistration(
				$this->user_id, $this->session_uniq, $timezone_utc_offset, $screen_avail_width, $screen_avail_height
			);
		} catch (cs_JoinLinkIsExpired|cs_IncorrectJoinLink|cs_JoinLinkNotFound) {

			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JOIN_LINK_VALIDATE);
			return $this->error(1199, "incorrect link");
		} catch (cs_JoinLinkIsNotActive|cs_JoinLinkIsUsed) {
			return $this->error(1211002, "inactive link");
		} catch (cs_UserAlreadyInCompany $e) {

			return $this->error(1203, "member is already in company", [
				"company_id"      => $e->getCompanyId(),
				"inviter_user_id" => $e->getInviterUserId(),
			]);
		} catch (cs_CompanyNotExist) {
			return $this->error(1102, "Company not found");
		} catch (Domain_Company_Exception_IsRelocating) {
			throw new \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException("company is relocating");
		} catch (Domain_Company_Exception_IsHibernated) {
			throw new \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException("company is hibernated");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new \BaseFrame\Exception\Request\CompanyNotServedException("company is not served");
		} catch (Domain_Link_Exception_LinkNotFound|Domain_InviteLink_Exception_InviteCodeNotExist) {
			throw new CaseException(1205001, "can't validate link");
		} catch (Domain_InviteLink_Exception_UserAlreadyAcceptInviteLink) {
			throw new CaseException(1210001, "user already accept invite code");
		} catch (Domain_InviteLink_Exception_InviteCodeExpired) {
			throw new CaseException(1205002, "the link has expired");
		} catch (Domain_Link_Exception_UserNotFinishRegistration) {
			throw new CaseException(1211001, "user not finish registration");
		} catch (Domain_InviteLink_Exception_InviteCodeCreatedByMe) {
			throw new CaseException(1210002, "invite code created by me");
		} catch (Domain_Link_Exception_SupportCompanyTemporarilyUnavailable $e) {

			// ссылка на компанию группу-поддержки недоступна в данный момент
			if (Type_System_Legacy::isSupportCompanyErrorAllowed()) {
				return $this->error(1205005, "support company temporarily unavailable", ["next_attempt_at" => $e->valid_till]);
			}
			return $this->error(1199, "incorrect link");
		} catch (BlockException|Domain_User_Exception_Attribution_LocalNetworkIpAddress) {

			return $this->ok([
				"action" => (string) Domain_User_Scenario_Api_AuthV2::ATTRIBUTION_ACTION_OPEN_DASHBOARD,
				"data"   => (object) [],
			]);
		}

		return $this->ok($output);
	}

	/**
	 * Попытаться авторизоваться с использование токена аутентификации.
	 */
	public function tryAuthenticationToken():array {

		$authentication_token = $this->post(\Formatter::TYPE_STRING, "authentication_token");

		if (!ServerProvider::isOnPremise()) {
			throw new EndpointAccessDeniedException("only for on-premise server");
		}

		Type_Antispam_Ip::check(Type_Antispam_User::TRY_AUTHENTICATION_TOKEN);

		try {

			// пытаемся активировать полученный токен
			[$user_id, $join_link_info] = Domain_Solution_Scenario_Api::tryAuthenticationToken($this->user_id, $authentication_token);
		} catch (Domain_Solution_Exception_ExpiredAuthenticationToken) {
			return $this->error(1215001, "token expired");
		} catch (cs_UserAlreadyBlocked|Domain_Solution_Exception_BadAuthenticationToken $e) {

			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_User::TRY_AUTHENTICATION_TOKEN);
			throw new ParamException($e->getMessage());}
		catch (cs_UserAlreadyLoggedIn) {
			return $this->error(1215002, "already logged");
		}

		$this->action->profile($user_id);
		return $this->ok([
			"join_link_info" => (object) $join_link_info,
		]);
	}

}