<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Методы для работы аутентификации через почту веб-сайта on-premise решений
 */
class Onpremiseweb_Auth_Mail extends \BaseFrame\Controller\Api {

	public const ECODE_JL_BAD       = 1711001;
	public const ECODE_JL_INACTIVE  = 1711002;
	public const ECODE_JL_TRY_LATER = 1711005;

	public const ECODE_UJL_ALREADY_ACCEPTED = 1711006;
	public const ECODE_UJL_ACCEPTED_BEFORE  = 1711002;

	public const ECODE_AUTH_EXPIRED      = 1708300; // процесс авторизации просрочен
	public const ECODE_AUTH_DONE         = 1708301; // авторизация уже завершена
	public const ECODE_AUTH_NEED_RESTART = 1708302; // необходимо перезапустить аутентификацию
	public const ECODE_AUTH_BLOCKED      = 1708399; // авторизация заблокирована

	public const ECODE_GRECPTACHA_REQUIRED  = 1708200; // нужно ввести каптчу
	public const ECODE_GRECPTACHA_INCORRECT = 1708201; // каптча не пройдена

	public const ECODE_UAUTH_LOGGED          = 1708100; // пользователь уже авторизован
	public const ECODE_UAUTH_BAD_CODE        = 1708112; // неверный код
	public const ECODE_UAUTH_CODE_DENIED     = 1708113; // неверный код
	public const ECODE_UAUTH_RESEND_PAUSED   = 1708114; // переотправку нужно подождать
	public const ECODE_UAUTH_BAD_MAIL        = 1708115; // некорректная почта
	public const ECODE_UAUTH_BAD_PASSWORD    = 1708116; // неверный пароль
	public const ECODE_UAUTH_BAD_INCORRECT   = 1708117; // некорректный пароль
	public const ECODE_UAUTH_RESEND_DENIED   = 1708199; // переотправку нужно подождать
	public const ECODE_UAUTH_REDIRECT_TO_SSO = 1708119; // перенаправляем на способ аутентификации через SSO

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"begin",
		"confirmShortAuthPassword",
		"confirmFullAuthPassword",
		"confirmFullAuthCode",
		"resendFullAuthCode",
		"cancel",
	];

	/**
	 * метод начала аутентификации через почту
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ReturnFatalException
	 * @throws ParamException
	 * @throws \blockException
	 * @long try ... catch
	 */
	public function begin():array {

		$mail                = $this->post(\Formatter::TYPE_STRING, "mail");
		$grecaptcha_response = $this->post(\Formatter::TYPE_STRING, "grecaptcha_response", false);
		$join_link           = $this->post(\Formatter::TYPE_STRING, "join_link", false);

		try {

			Type_Antispam_Ip::check(Type_Antispam_Ip::BEGIN_INCORRECT_MAIL);

			// сценарий регистрации
			Gateway_Bus_CollectorAgent::init()->inc("row0");
			[$auth_info, $validation_result, $scenario] = Domain_User_Scenario_OnPremiseWeb_Auth_Mail::begin(
				$this->user_id, $mail, $grecaptcha_response, $join_link
			);
		} catch (cs_blockException $e) {

			throw new CaseException(423, "begin method limit exceeded", [
				"expires_at" => $e->getNextAttempt(),
			]);
		} catch (cs_UserAlreadyLoggedIn) {
			return $this->error(static::ECODE_UAUTH_LOGGED, "user already logged in");
		} catch (InvalidMail|Domain_User_Exception_AuthStory_Mail_DomainNotAllowed) {

			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::BEGIN_INCORRECT_MAIL);

			return $this->error(static::ECODE_UAUTH_BAD_MAIL, "invalid mail [$mail]");
		} catch (Domain_User_Exception_AuthStory_RegistrationWithoutInvite) {

			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::BEGIN_INCORRECT_MAIL);
			return $this->error(1000, "registration is not allowed without invite");
		} catch (cs_RecaptchaIsRequired) {
			return $this->error(static::ECODE_GRECPTACHA_REQUIRED, "need grecaptcha_response in request");
		} catch (cs_WrongRecaptcha) {
			return $this->error(static::ECODE_GRECPTACHA_INCORRECT, "not valid captcha. Try again");
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
		} catch (Domain_User_Exception_AuthStory_RedirectToSso) {
			return $this->error(static::ECODE_UAUTH_REDIRECT_TO_SSO, "redirect to sso");
		}

		return $this->ok([
			"auth_info"      => (object) Onpremiseweb_Format::authInfo($auth_info),
			"join_link_info" => $validation_result === false ? "null" : Onpremiseweb_Format::joinLinkInfo($validation_result),
			"scenario"       => (string) $scenario,
		]);
	}

	/**
	 * подтверждение паролем аутентификации через почту для короткого сценария в Compass On-premise
	 * используется если отключена опция подтверждения через проверочный код в конфиге
	 *
	 * @return array
	 * @throws CaseException
	 * @throws Domain_User_Exception_AuthStory_Mail_ShortConfirmScenarioNotAllowed
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_Mismatch
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_PlatformNotFound
	 * @throws cs_UnknownKeyType
	 * @long
	 */
	public function confirmShortAuthPassword():array {

		$auth_key            = $this->post(\Formatter::TYPE_STRING, "auth_key");
		$password            = $this->post(\Formatter::TYPE_STRING, "password");
		$grecaptcha_response = $this->post(\Formatter::TYPE_STRING, "grecaptcha_response", false);
		$join_link_uniq      = $this->post(\Formatter::TYPE_STRING, "join_link_uniq", false);

		try {
			$auth_map = Type_Pack_Main::replaceKeyWithMap("auth_key", $auth_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {

			[$authentication_token, $is_empty_profile, $integration_action_list] = Domain_User_Scenario_OnPremiseWeb_Auth_Mail
				::confirmShortAuthPassword($this->user_id, $auth_map, $password, $grecaptcha_response, $join_link_uniq);
		} catch (cs_AuthAlreadyFinished) {

			// аутентификация уже завершена
			return $this->error(static::ECODE_AUTH_DONE, "auth is already finished");
		} catch (cs_AuthIsExpired|cs_WrongAuthKey) {

			// аутентификация невалидна
			return $this->error(static::ECODE_AUTH_EXPIRED, "auth is expired");
		} catch (Domain_User_Exception_AuthStory_WrongPassword $e) {

			// прислан неправильный пароль
			return $this->error(static::ECODE_UAUTH_BAD_PASSWORD, "not equal password", [
				"available_attempts" => $e->getAvailableAttempts(),
			]);
		} catch (cs_AuthIsBlocked $e) {

			// попытка аутентификации заблокирована из-за превышения лимита попыток ввода
			return $this->error(static::ECODE_AUTH_BLOCKED, "auth blocked", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_UserAlreadyLoggedIn) {
			return $this->error(static::ECODE_UAUTH_LOGGED, "already logged");
		} catch (InvalidMail|Domain_User_Exception_Mail_BelongAnotherUser|cs_DamagedActionException|cs_InvalidHashStruct $e) {
			throw new ReturnFatalException("internal error occurred: " . $e->getMessage());
		} catch (cs_JoinLinkNotFound) {
			return $this->error(static::ECODE_JL_BAD, "bad join link");
		} catch (Domain_User_Exception_AuthStory_Mail_ShortConfirmScenarioNotAllowed) {
			return $this->error(static::ECODE_AUTH_NEED_RESTART, "auth restart needed");
		} catch (Domain_User_Exception_Password_Incorrect) {
			return $this->error(static::ECODE_UAUTH_BAD_INCORRECT, "incorrect password");
		} catch (Domain_User_Exception_AuthStory_TypeMismatch) {
			throw new ParamException("bad application behaviour");
		} catch (cs_RecaptchaIsRequired) {
			return $this->error(static::ECODE_GRECPTACHA_REQUIRED, "need grecaptcha_response in request");
		} catch (cs_WrongRecaptcha) {
			return $this->error(static::ECODE_GRECPTACHA_INCORRECT, "not valid captcha. Try again");
		}

		return $this->ok([
			"authentication_token"    => (string) $authentication_token,
			"need_fill_profile"       => (int) $is_empty_profile,
			"integration_action_list" => (array) $integration_action_list,
		]);
	}

	/**
	 * подтверждаем паролем попытку аутентификации по полному сценарию
	 *
	 * @return array
	 * @throws CaseException
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_Incorrect
	 * @throws Domain_User_Exception_Password_Mismatch
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsBlocked
	 * @throws cs_AuthIsExpired
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_PlatformNotFound
	 * @throws cs_UnknownKeyType
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 * @long
	 */
	public function confirmFullAuthPassword():array {

		$auth_key            = $this->post(\Formatter::TYPE_STRING, "auth_key");
		$password            = $this->post(\Formatter::TYPE_STRING, "password");
		$grecaptcha_response = $this->post(\Formatter::TYPE_STRING, "grecaptcha_response", false);

		try {
			$auth_map = Type_Pack_Main::replaceKeyWithMap("auth_key", $auth_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {

			$auth_info = Domain_User_Scenario_OnPremiseWeb_Auth_Mail
				::confirmFullAuthPassword($this->user_id, $auth_map, $password, $grecaptcha_response);
		} catch (Domain_User_Exception_AuthStory_StageNotAllowed|Domain_User_Exception_AuthStory_TypeMismatch) {
			throw new ParamException("bad application behaviour");
		} catch (cs_AuthAlreadyFinished) {

			// аутентификация уже завершена
			return $this->error(static::ECODE_AUTH_DONE, "auth is already finished");
		} catch (cs_AuthIsExpired|cs_WrongAuthKey) {

			// аутентификация невалидна
			return $this->error(static::ECODE_AUTH_EXPIRED, "auth is expired");
		} catch (Domain_User_Exception_AuthStory_WrongPassword $e) {

			// прислан неправильный пароль
			return $this->error(static::ECODE_UAUTH_BAD_PASSWORD, "not equal password", [
				"available_attempts" => $e->getAvailableAttempts(),
			]);
		} catch (cs_AuthIsBlocked $e) {

			// попытка аутентификации заблокирована из-за превышения лимита попыток ввода
			return $this->error(static::ECODE_AUTH_BLOCKED, "auth blocked", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_UserAlreadyLoggedIn) {
			return $this->error(static::ECODE_UAUTH_LOGGED, "already logged");
		} catch (InvalidMail|Domain_User_Exception_Mail_BelongAnotherUser|cs_DamagedActionException|cs_InvalidHashStruct $e) {
			throw new ReturnFatalException("internal error occurred: " . $e->getMessage());
		} catch (cs_JoinLinkNotFound) {
			return $this->error(static::ECODE_JL_BAD, "bad join link");
		} catch (Domain_User_Exception_Password_Incorrect) {
			return $this->error(static::ECODE_UAUTH_BAD_INCORRECT, "incorrect password");
		} catch (Domain_User_Exception_AuthStory_Mail_FullConfirmScenarioNotAllowed) {
			return $this->error(static::ECODE_AUTH_NEED_RESTART, "auth restart needed");
		} catch (cs_RecaptchaIsRequired) {
			return $this->error(static::ECODE_GRECPTACHA_REQUIRED, "need grecaptcha_response in request");
		} catch (cs_WrongRecaptcha) {
			return $this->error(static::ECODE_GRECPTACHA_INCORRECT, "not valid captcha. Try again");
		}

		return $this->ok([
			"auth_info" => Onpremiseweb_Format::authInfo($auth_info),
		]);
	}

	/**
	 * Подтверждаем кодом аутентификацию через почту по полному сценарию
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_CacheIsEmpty
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidConfirmCode
	 * @throws cs_UnknownKeyType
	 * @long
	 */
	public function confirmFullAuthCode():array {

		$auth_key       = $this->post(\Formatter::TYPE_STRING, "auth_key");
		$code           = $this->post(\Formatter::TYPE_STRING, "code");
		$join_link_uniq = $this->post(\Formatter::TYPE_STRING, "join_link_uniq", false);

		try {
			$auth_map = Type_Pack_Main::replaceKeyWithMap("auth_key", $auth_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {

			[$authentication_token, $is_empty_profile, $integration_action_list] = Domain_User_Scenario_OnPremiseWeb_Auth_Mail
				::confirmFullAuthCode($this->user_id, $auth_map, $code, $join_link_uniq);
		} catch (Domain_User_Exception_AuthStory_StageNotAllowed|Domain_User_Exception_AuthStory_TypeMismatch) {
			throw new ParamException("bad application behaviour");
		} catch (cs_AuthAlreadyFinished) {

			// аутентификация уже завершена
			return $this->error(static::ECODE_AUTH_DONE, "auth is already finished");
		} catch (cs_AuthIsExpired|cs_WrongAuthKey) {

			// аутентификация невалидна
			return $this->error(static::ECODE_AUTH_EXPIRED, "auth is expired");
		} catch (cs_InvalidConfirmCode) {
			return $this->error(static::ECODE_UAUTH_BAD_CODE, "invalid code format");
		} catch (cs_AuthIsBlocked $e) {

			// попытка аутентификации заблокирована из-за превышения лимита попыток ввода
			return $this->error(static::ECODE_AUTH_BLOCKED, "auth blocked", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_UserAlreadyLoggedIn) {
			return $this->error(static::ECODE_UAUTH_LOGGED, "already logged");
		} catch (InvalidMail|Domain_User_Exception_Mail_BelongAnotherUser|cs_DamagedActionException|cs_InvalidHashStruct $e) {
			throw new ReturnFatalException("internal error occurred: " . $e->getMessage());
		} catch (cs_JoinLinkNotFound) {
			return $this->error(static::ECODE_JL_BAD, "bad join link");
		} catch (Domain_User_Exception_AuthStory_Mail_FullConfirmScenarioNotAllowed) {
			return $this->error(static::ECODE_AUTH_NEED_RESTART, "auth restart needed");
		} catch (cs_WrongCode $e) {

			// неправильный код подтверждения
			return $this->error(static::ECODE_UAUTH_CODE_DENIED, "incorrect code", [
				"available_attempts" => $e->getAvailableAttempts(),
				"next_attempt"       => $e->getNextAttempt(),
			]);
		}

		return $this->ok([
			"authentication_token"    => (string) $authentication_token,
			"need_fill_profile"       => (int) $is_empty_profile,
			"integration_action_list" => (array) $integration_action_list,
		]);
	}

	/**
	 * Переотправляем код аутентификации через почту по полному сценарию
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_ResendCodeCountLimitExceeded
	 * @throws cs_ResendWillBeAvailableLater
	 * @long
	 */
	public function resendFullAuthCode():array {

		$auth_key = $this->post(\Formatter::TYPE_STRING, "auth_key");

		try {
			$auth_map = Type_Pack_Main::replaceKeyWithMap("auth_key", $auth_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {
			$auth_info = Domain_User_Scenario_OnPremiseWeb_Auth_Mail::resendFullAuthCode($this->user_id, $auth_map);
		} catch (Domain_User_Exception_AuthStory_StageNotAllowed) {
			throw new ParamException("bad application behaviour");
		} catch (cs_AuthAlreadyFinished) {

			// аутентификация уже завершена
			return $this->error(static::ECODE_AUTH_DONE, "auth is already finished");
		} catch (cs_AuthIsExpired|cs_WrongAuthKey) {

			// аутентификация невалидна
			return $this->error(static::ECODE_AUTH_EXPIRED, "auth is expired");
		} catch (cs_AuthIsBlocked $e) {

			// попытка аутентификации заблокирована из-за превышения лимита попыток ввода
			return $this->error(static::ECODE_AUTH_BLOCKED, "auth blocked", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_UserAlreadyLoggedIn) {
			return $this->error(static::ECODE_UAUTH_LOGGED, "already logged");
		} catch (Domain_User_Exception_AuthStory_Mail_FullConfirmScenarioNotAllowed) {
			return $this->error(static::ECODE_AUTH_NEED_RESTART, "auth restart needed");
		} catch (Domain_User_Exception_AuthStory_ResendCountLimitExceeded $e) {

			return $this->error(static::ECODE_UAUTH_RESEND_DENIED, "resend count limit", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_ResendWillBeAvailableLater $e) {

			return $this->error(static::ECODE_UAUTH_RESEND_PAUSED, "resend will be available later", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (Domain_User_Exception_AuthStory_TypeMismatch) {
			throw new ParamException("bad application behaviour");
		}

		return $this->ok([
			"auth_info" => Onpremiseweb_Format::authInfo($auth_info),
		]);
	}

	/**
	 * отменяем/отвязываем попытку аутентификации от пользователя
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function cancel():array {

		$auth_key = $this->post(\Formatter::TYPE_STRING, "auth_key");

		try {
			$auth_map = Type_Pack_Main::replaceKeyWithMap("auth_key", $auth_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {
			Domain_User_Scenario_OnPremiseWeb_Auth_Mail::cancel($this->user_id, $auth_map);
		} catch (cs_WrongAuthKey) {

			// аутентификация невалидна
			return $this->error(static::ECODE_AUTH_EXPIRED, "auth is expired");
		} catch (cs_UserAlreadyLoggedIn) {
			return $this->error(static::ECODE_UAUTH_LOGGED, "already logged");
		}

		return $this->ok();
	}
}