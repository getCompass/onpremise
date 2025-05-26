<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Class ApiV1_Pivot_Auth
 */
class Apiv1_Pivot_Auth extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"doStart",
		"tryConfirm",
		"doResend",
		"doLogout",
	];

	/**
	 * Регистрация пользователя.
	 *
	 * @throws \blockException
	 */
	public function doStart():array {

		// получаем данные
		$phone_number        = $this->post(\Formatter::TYPE_STRING, "phone_number");
		$grecaptcha_response = $this->post(\Formatter::TYPE_STRING, "grecaptcha_response", false);

		try {

			// сценарий регистрации
			Gateway_Bus_CollectorAgent::init()->inc("row0");
			$auth_info = Domain_User_Scenario_Api::startAuth($this->user_id, $phone_number, $grecaptcha_response);
		} catch (cs_UserAlreadyLoggedIn) {

			Gateway_Bus_CollectorAgent::init()->inc("row1");
			return $this->error(1, "user already logged in");
		} catch (\BaseFrame\Exception\Domain\InvalidPhoneNumber) {

			Gateway_Bus_CollectorAgent::init()->inc("row2");
			return $this->error(201, "invalid phone_number [$phone_number]");
		} catch (cs_RecaptchaIsRequired) {

			Gateway_Bus_CollectorAgent::init()->inc("row3");
			return $this->error(4, "need grecaptcha_response in request");
		} catch (cs_WrongRecaptcha) {

			Gateway_Bus_CollectorAgent::init()->inc("row4");
			return $this->error(3, "not valid captcha. Try again");
		} catch (cs_AuthIsBlocked $e) {

			Gateway_Bus_CollectorAgent::init()->inc("row5");
			return $this->error(1004, "auth blocked", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_ActionNotAvailable) {
			return $this->error(105, "action not available");
		} catch (cs_PlatformNotFound) {
			throw new \BaseFrame\Exception\Request\ParamException("passed unknown platform");
		} catch (Domain_User_Exception_UserBanned|Domain_User_Exception_PhoneBanned) {
			return $this->error(1905, "action not available");
		}

		// пишем статистику об успешной регистрации авторизации пользователя
		if ($auth_info->auth->type == Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER) {
			Gateway_Bus_CollectorAgent::init()->inc("row6");
		} else {
			Gateway_Bus_CollectorAgent::init()->inc("row7");
		}

		return $this->ok(Apiv1_Pivot_Format::authInfo($auth_info));
	}

	/**
	 * Подтверждение смс
	 */
	public function tryConfirm():array {

		// получаем данные
		$sms_code = $this->post(\Formatter::TYPE_STRING, "sms_code");
		$auth_key = $this->post(\Formatter::TYPE_STRING, "auth_key");
		$auth_map = Type_Pack_Main::replaceKeyWithMap("auth_key", $auth_key);

		try {

			// сценарий подтверждения смс
			Gateway_Bus_CollectorAgent::init()->inc("row8");
			$user_id = Domain_User_Scenario_Api::tryConfirmAuth($this->user_id, $auth_map, $sms_code);
		} catch (cs_AuthAlreadyFinished) {

			Gateway_Bus_CollectorAgent::init()->inc("row9");
			return $this->error(17, "auth is already finished");
		} catch (cs_AuthIsExpired|cs_WrongAuthKey) {

			Gateway_Bus_CollectorAgent::init()->inc("row10");
			return $this->error(18, "auth is expired");
		} catch (cs_InvalidConfirmCode) {

			Gateway_Bus_CollectorAgent::init()->inc("row11");
			return $this->error(19, "invalid code format");
		} catch (cs_UserAlreadyLoggedIn) {

			Gateway_Bus_CollectorAgent::init()->inc("row12");
			return $this->error(1, "user already logged in");
		} catch (cs_AuthIsBlocked $e) {

			Gateway_Bus_CollectorAgent::init()->inc("row13");
			return $this->error(1004, "auth blocked", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_WrongCode $e) {

			Gateway_Bus_CollectorAgent::init()->inc("row14");
			return $this->error(7, "incorrect code", [
				"available_attempts" => $e->getAvailableAttempts(),
				"next_attempt"       => $e->getNextAttempt(),
			]);
		} catch (Domain_User_Exception_UserBanned|Domain_User_Exception_PhoneBanned) {
			return $this->error(1905, "action not available");
		}

		$this->action->profile($user_id);
		Gateway_Bus_CollectorAgent::init()->inc("row15");
		Gateway_Bus_CollectorAgent::init()->inc("row70");
		return $this->ok();
	}

	/**
	 * Переотправка смс
	 */
	public function doResend():array {

		$auth_key            = $this->post(\Formatter::TYPE_STRING, "auth_key");
		$auth_map            = Type_Pack_Main::replaceKeyWithMap("auth_key", $auth_key);
		$grecaptcha_response = $this->post(\Formatter::TYPE_STRING, "grecaptcha_response", false);

		try {

			// отправляем смс, записываем данные в базу, получаем данные для клиента
			Gateway_Bus_CollectorAgent::init()->inc("row16");
			$auth_info = Domain_User_Scenario_Api::resendCode($this->user_id, $auth_map, $grecaptcha_response);
		} catch (cs_AuthAlreadyFinished) {

			Gateway_Bus_CollectorAgent::init()->inc("row17");
			return $this->error(17, "auth is already finished");
		} catch (cs_AuthIsExpired) {

			Gateway_Bus_CollectorAgent::init()->inc("row18");
			return $this->error(18, "auth is expired");
		} catch (cs_UserAlreadyLoggedIn) {

			Gateway_Bus_CollectorAgent::init()->inc("row19");
			return $this->error(1, "user already logged in");
		} catch (cs_RecaptchaIsRequired) {

			Gateway_Bus_CollectorAgent::init()->inc("row20");
			return $this->error(4, "need grecaptcha_response in request");
		} catch (cs_WrongRecaptcha) {

			Gateway_Bus_CollectorAgent::init()->inc("row21");
			return $this->error(3, "not valid captcha. Try again");
		} catch (cs_ResendCodeCountLimitExceeded) {

			Gateway_Bus_CollectorAgent::init()->inc("row22");
			return $this->error(55, "resend count limit");
		} catch (cs_AuthIsBlocked $e) {

			Gateway_Bus_CollectorAgent::init()->inc("row23");
			return $this->error(1004, "auth blocked", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_ResendWillBeAvailableLater $e) {

			Gateway_Bus_CollectorAgent::init()->inc("row24");
			return $this->error(52, "resend will be available later", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		}

		Gateway_Bus_CollectorAgent::init()->inc("row25");
		return $this->ok(Apiv1_Pivot_Format::authInfo($auth_info));
	}

	/**
	 * Закрывает текущую сессию пользователя.
	 * @throws
	 */
	public function doLogout():array {

		try {
			Domain_User_Scenario_Api::doLogout($this->user_id);
		} catch (\cs_RowIsEmpty) {

			// если клиент ожидает 401, то возвращаем ему 401
			if (\BaseFrame\Http\Header\AuthorizationControl::parse()::expect401()) {
				throw new EndpointAccessDeniedException("User not authorized for this actions.");
			}

			// иногда клиенты не хотят 401 и им нужно запустить
			// весь флоу валидации сессии через start + doStart
			throw new cs_AnswerCommand("need_call_start", []);
		}

		Gateway_Bus_CollectorAgent::init()->inc("row26");
		return $this->ok();
	}
}