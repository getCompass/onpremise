<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Методы для работы аутентификации через SSO
 */
class Onpremiseweb_Auth_Sso extends \BaseFrame\Controller\Api {

	public const ECODE_JL_BAD       = 1711001;
	public const ECODE_JL_INACTIVE  = 1711002;
	public const ECODE_JL_TRY_LATER = 1711005;

	public const ECODE_UJL_ACCEPTED_BEFORE = 1711002;

	public const ECODE_AUTH_EXPIRED          = 1708300; // процесс авторизации просрочен
	public const ECODE_UAUTH_LOGGED          = 1708100; // пользователь уже авторизован
	public const ECODE_UAUTH_METHOD_DISABLED = 1708118; // способ аутентификации отключен

	public const ECODE_UAUTH_SSO_INCORRECT_FULL_NAME = 1708120; // не удалось подтянуть корректные Имя Фамилия из SSO провайдера

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"begin",
	];

	/**
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CaseException
	 * @throws ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @long
	 */
	public function begin():array {

		$sso_auth_token = $this->post(\Formatter::TYPE_STRING, "sso_auth_token");
		$signature      = $this->post(\Formatter::TYPE_STRING, "signature");
		$join_link      = $this->post(\Formatter::TYPE_STRING, "join_link", false);

		try {
			[$authentication_token, $is_registration, $user_info, $integration_action_list] = Domain_User_Scenario_OnPremiseWeb_Auth_Sso
				::begin($this->user_id, $sso_auth_token, $signature, $join_link, $this->session_uniq);
		} catch (cs_UserAlreadyLoggedIn) {
			return $this->error(static::ECODE_UAUTH_LOGGED, "user already logged in");
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			return $this->error(self::ECODE_UAUTH_METHOD_DISABLED, "auth method disabled");
		} catch (Domain_Link_Exception_TemporaryUnavailable) {
			return $this->error(static::ECODE_JL_TRY_LATER, "try later");
		} catch (Domain_User_Exception_AuthStory_Expired) {
			return $this->error(static::ECODE_AUTH_EXPIRED, "auth is expired");
		} catch (Domain_User_Exception_AuthStory_Sso_SignatureMismatch|Domain_User_Exception_AuthStory_Sso_UnexpectedBehaviour) {
			throw new ParamException("wrong parameters");
		} catch (cs_JoinLinkIsNotActive) {
			return $this->error(static::ECODE_JL_INACTIVE, "inactive join link");
		} catch (cs_JoinLinkIsUsed) {
			return $this->error(static::ECODE_UJL_ACCEPTED_BEFORE, "already used by user");
		} catch (cs_IncorrectJoinLink|cs_JoinLinkNotFound) {
			return $this->error(static::ECODE_JL_BAD, "bad join link");
		} catch (cs_UserNotFound $e) {
			throw new ReturnFatalException("unhandled error {$e->getMessage()}");
		} catch (BlockException $e) {

			return $this->error(423, "limit exceeded", [
				"expires_at" => $e->getExpire(),
			]);
		} catch (Domain_User_Exception_AuthStory_Sso_IncorrectFullName) {

			return $this->error(static::ECODE_UAUTH_SSO_INCORRECT_FULL_NAME, "incorrect full name", [
				"sso_protocol" => Domain_User_Entity_Auth_Config::getSsoProtocol(),
			]);
		}

		return $this->ok([
			"authentication_token"    => (string) $authentication_token,
			"is_registration"         => (int) $is_registration,
			"user_info"               => (object) Onpremiseweb_Format::userInfo($user_info),
			"integration_action_list" => (array) $integration_action_list,
		]);
	}
}