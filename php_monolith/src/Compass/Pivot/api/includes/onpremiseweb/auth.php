<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\CountryNotFound;
use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Методы для работы аутентификации веб-сайта on-premise решений.
 */
class Onpremiseweb_Auth extends \BaseFrame\Controller\Api {

	public const ECODE_JL_BAD       = 1711001;
	public const ECODE_JL_INACTIVE  = 1711002;
	public const ECODE_JL_TRY_LATER = 1711005;

	public const ECODE_UJL_ALREADY_ACCEPTED = 1711006;
	public const ECODE_UJL_ACCEPTED_BEFORE  = 1711002;

	public const ECODE_AUTH_EXPIRED = 1708300; // процесс авторизации просрочен
	public const ECODE_AUTH_DONE    = 1708301; // авторизация уже завершена
	public const ECODE_AUTH_BLOCKED = 1708399; // авторизация заблокирована

	public const ECODE_GRECPTACHA_REQUIRED  = 1708200; // нужно ввести каптчу
	public const ECODE_GRECPTACHA_INCORRECT = 1708201; // каптча не пройдена

	public const ECODE_UAUTH_LOGGED        = 1708100; // пользователь уже авторизован
	public const ECODE_UAUTH_BAD_PHONE     = 1708111; // неверный номер телефона
	public const ECODE_UAUTH_BAD_CODE      = 1708112; // неверный код
	public const ECODE_UAUTH_CODE_DENIED   = 1708113; // неверный код
	public const ECODE_UAUTH_RESEND_PAUSED = 1708114; // переотправку нужно подождать
	public const ECODE_UAUTH_RESEND_DENIED = 1708199; // переотправку нужно подождать

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"begin",
		"confirm",
		"retry",
		"logout",
		"generateToken",
	];

	/**
	 * Метод начала процесса аутентификации.
	 * @long try..catch
	 */
	public function begin():array {

		$phone_number        = $this->post(\Formatter::TYPE_STRING, "phone_number");
		$grecaptcha_response = $this->post(\Formatter::TYPE_STRING, "grecaptcha_response", false);
		$join_link           = $this->post(\Formatter::TYPE_STRING, "join_link", false);

		try {

			Type_Antispam_Ip::check(Type_Antispam_Ip::BEGIN_INCORRECT_PHONE_NUMBER);

			// сценарий регистрации
			Gateway_Bus_CollectorAgent::init()->inc("row0");
			[$auth_info, $validation_result] = Domain_User_Scenario_OnPremiseWeb::beginAuthentication(
				$this->user_id, $phone_number, $grecaptcha_response, $join_link
			);
		} catch (cs_blockException $e) {

			throw new CaseException(423, "begin method limit exceeded", [
				"expires_at" => $e->getNextAttempt(),
			]);
		} catch (cs_UserAlreadyLoggedIn) {
			return $this->error(static::ECODE_UAUTH_LOGGED, "user already logged in");
		} catch (InvalidPhoneNumber) {

			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::BEGIN_INCORRECT_PHONE_NUMBER);
			return $this->error(static::ECODE_UAUTH_BAD_PHONE, "invalid phone_number [$phone_number]");
		} catch (Domain_User_Exception_AuthStory_RegistrationWithoutInvite) {

			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::BEGIN_INCORRECT_PHONE_NUMBER);
			return $this->error(1000, "registration is not allowed without invite");
		} catch (cs_RecaptchaIsRequired) {
			return $this->error(static::ECODE_GRECPTACHA_REQUIRED, "need grecaptcha_response in request");
		} catch (cs_WrongRecaptcha) {
			return $this->error(static::ECODE_GRECPTACHA_INCORRECT, "not valid captcha. Try again");
		} catch (cs_AuthIsBlocked $e) {
			return $this->error(static::ECODE_AUTH_BLOCKED, "auth blocked", ["next_attempt" => $e->getNextAttempt(),]);
		} catch (LocaleTextNotFound|cs_ActionNotAvailable|cs_PlatformNotFound|\blockException $e) {
			throw new ReturnFatalException("internal error occurred: " . $e->getMessage());
		} catch (Domain_Link_Exception_TemporaryUnavailable) {
			return $this->error(static::ECODE_JL_TRY_LATER, "try later");
		} catch (cs_IncorrectJoinLink|cs_JoinLinkNotFound) {
			return $this->error(static::ECODE_JL_BAD, "bad join link");
		} catch (cs_JoinLinkIsNotActive) {
			return $this->error(static::ECODE_JL_INACTIVE, "inactive join link");
		} catch (cs_JoinLinkIsUsed) {
			return $this->error(static::ECODE_UJL_ACCEPTED_BEFORE, "already user by user");
		} catch (cs_UserAlreadyInCompany) {
			return $this->error(static::ECODE_UJL_ALREADY_ACCEPTED, "already company member");
		} catch (cs_UserNotFound $e) {
			throw new ReturnFatalException("unhandled error {$e->getMessage()}");
		}

		return $this->ok([
			"auth_info"      => Onpremiseweb_Format::authInfo($auth_info),
			"join_link_info" => $validation_result === false ? "null" : Onpremiseweb_Format::joinLinkInfo($validation_result),
		]);
	}

	/**
	 * Метод повторного запроса кода.
	 * @long try..catch
	 */
	public function retry():array {

		$auth_key            = $this->post(\Formatter::TYPE_STRING, "auth_key");
		$grecaptcha_response = $this->post(\Formatter::TYPE_STRING, "grecaptcha_response", false);

		try {
			$auth_map = Type_Pack_Main::replaceKeyWithMap("auth_key", $auth_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {

			/** @var Struct_User_Auth_Info $auth_info */
			$auth_info = Domain_User_Scenario_OnPremiseWeb::resendAuthenticationCode(
				$this->user_id, $auth_map, $grecaptcha_response
			);
		} catch (cs_AuthAlreadyFinished) {

			Gateway_Bus_CollectorAgent::init()->inc("row17");
			return $this->error(static::ECODE_UAUTH_LOGGED, "auth is already finished");
		} catch (cs_AuthIsExpired|cs_WrongAuthKey) {

			Gateway_Bus_CollectorAgent::init()->inc("row18");
			return $this->error(static::ECODE_AUTH_EXPIRED, "auth is expired");
		} catch (cs_RecaptchaIsRequired) {

			Gateway_Bus_CollectorAgent::init()->inc("row20");
			return $this->error(static::ECODE_GRECPTACHA_REQUIRED, "need grecaptcha_response in request");
		} catch (cs_WrongRecaptcha) {

			Gateway_Bus_CollectorAgent::init()->inc("row21");
			return $this->error(static::ECODE_GRECPTACHA_INCORRECT, "not valid captcha. Try again");
		} catch (cs_ResendCodeCountLimitExceeded) {

			Gateway_Bus_CollectorAgent::init()->inc("row22");
			return $this->error(static::ECODE_UAUTH_RESEND_DENIED, "resend count limit");
		} catch (cs_AuthIsBlocked $e) {

			Gateway_Bus_CollectorAgent::init()->inc("row23");
			return $this->error(static::ECODE_AUTH_BLOCKED, "auth blocked", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_ResendWillBeAvailableLater $e) {

			Gateway_Bus_CollectorAgent::init()->inc("row24");
			return $this->error(static::ECODE_UAUTH_RESEND_PAUSED, "resend will be available later", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (CountryNotFound|InvalidPhoneNumber|LocaleTextNotFound|cs_PlatformNotFound $e) {
			throw new ReturnFatalException("unhandled error {$e->getMessage()}");
		}

		Gateway_Bus_CollectorAgent::init()->inc("row25");
		return $this->ok(Onpremiseweb_Format::authInfo($auth_info));
	}

	/**
	 * Метод подтверждения аутентификации.
	 * @long try..catch
	 */
	public function confirm():array {

		// получаем данные
		$sms_code       = $this->post(\Formatter::TYPE_STRING, "sms_code");
		$auth_key       = $this->post(\Formatter::TYPE_STRING, "auth_key");
		$join_link_uniq = $this->post(\Formatter::TYPE_STRING, "join_link_uniq", false);

		try {
			$auth_map = Type_Pack_Main::replaceKeyWithMap("auth_key", $auth_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {

			// сценарий подтверждения смс
			Gateway_Bus_CollectorAgent::init()->inc("row8");
			[$authentication_token, $is_empty_profile] = Domain_User_Scenario_OnPremiseWeb::confirmAuthentication(
				$this->user_id, $auth_map, $sms_code, $join_link_uniq
			);
		} catch (cs_AuthAlreadyFinished) {

			// аутентификация уже завершена
			return $this->error(static::ECODE_AUTH_DONE, "auth is already finished");
		} catch (cs_AuthIsExpired|cs_WrongAuthKey) {

			// аутентификация невалидна
			return $this->error(static::ECODE_AUTH_EXPIRED, "auth is expired");
		} catch (cs_InvalidConfirmCode) {

			// неправильный формат код подтверждения
			return $this->error(static::ECODE_UAUTH_BAD_CODE, "invalid code format");
		} catch (cs_AuthIsBlocked $e) {

			// номер заблокирован из-за превышения лимита попыток
			return $this->error(static::ECODE_AUTH_BLOCKED, "auth blocked", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_WrongCode $e) {

			// неправильный код подтверждения
			return $this->error(static::ECODE_UAUTH_CODE_DENIED, "incorrect code", [
				"available_attempts" => $e->getAvailableAttempts(),
				"next_attempt"       => $e->getNextAttempt(),
			]);
		} catch (cs_UserAlreadyLoggedIn) {
			return $this->error(static::ECODE_UAUTH_LOGGED, "already logged");
		} catch (InvalidPhoneNumber|Domain_User_Exception_PhoneNumberBinding|cs_DamagedActionException|cs_InvalidHashStruct $e) {
			throw new ReturnFatalException("internal error occurred: " . $e->getMessage());
		} catch (cs_JoinLinkNotFound) {
			return $this->error(static::ECODE_JL_BAD, "bad join link");
		}

		return $this->ok([
			"authentication_token" => $authentication_token,
			"need_fill_profile"    => (int) $is_empty_profile,
		]);
	}

	/**
	 * Завершает пользовательскую сессию.
	 */
	public function logout():array {

		Domain_User_Scenario_OnPremiseWeb::logout($this->user_id);
		return $this->ok();
	}

	/**
	 * Выполняет проверку наличия сессии.
	 */
	public function generateToken():array {

		$join_link_uniq = $this->post(\Formatter::TYPE_STRING, "join_link_uniq", false);

		if ($this->user_id === 0) {
			throw new EndpointAccessDeniedException("need authenticate first");
		}

		$token = Domain_Solution_Scenario_OnPremiseWeb::generateAuthenticationToken($this->user_id, $join_link_uniq);
		return $this->ok(["authentication_token" => $token]);
	}
}