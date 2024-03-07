<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Class ApiV1_Pivot_Security
 */
class Apiv1_Pivot_Security extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getUserCompanySessionToken",
		"trySendTwoFaSms",
		"tryConfirmTwoFaSms",
		"tryResendTwoFaSms",
	];

	/**
	 * получить токен
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function getUserCompanySessionToken():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			$user_company_session_token = Domain_Company_Scenario_Api::getUserCompanySessionToken($this->user_id, $this->session_uniq, $company_id);
		} catch (cs_CompanyUserIsNotFound) {
			return $this->error(1002, "User is not a member of the company");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("invalid company id");
		}

		return $this->ok([
			"user_company_session_token" => (string) $user_company_session_token,
		]);
	}

	/**
	 * Метод для отправки смс для подтверждения 2fa действия
	 *
	 * @throws ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_PlatformNotFound
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws cs_UnknownKeyType
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_WrongAuthKey
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function trySendTwoFaSms():array {

		$two_fa_key          = $this->post(\Formatter::TYPE_STRING, "two_fa_key");
		$two_fa_map          = Type_Pack_Main::replaceKeyWithMap("two_fa_key", $two_fa_key);
		$grecaptcha_response = $this->post(\Formatter::TYPE_STRING, "grecaptcha_response", false);

		try {
			$two_fa_story = Domain_User_Scenario_Api::trySendTwoFaSms($this->user_id, $two_fa_map, $grecaptcha_response);
		} catch (cs_TwoFaIsExpired) {
			return $this->error(2300, "2fa key is expired");
		} catch (cs_TwoFaIsFinished) {
			return $this->error(2301, "2fa action already finished");
		} catch (cs_TwoFaInvalidUser|cs_WrongTwoFaKey) {
			return $this->error(2302, "2fa key is invalid");
		} catch (cs_RecaptchaIsRequired) {

			Gateway_Bus_CollectorAgent::init()->inc("row76");
			return $this->error(4, "need grecaptcha_response in request");
		} catch (cs_WrongRecaptcha) {
			return $this->error(3, "not valid captcha. Try again");
		} catch (cs_UserPhoneSecurityNotFound) {
			throw new ParamException("not found phone for user");
		}

		return $this->ok(Apiv1_Pivot_Format::twoFaStoryInfo($two_fa_story));
	}

	/**
	 * Метод для подтверждения смс для 2fa действия
	 *
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 * @throws cs_UnknownKeyType
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function tryConfirmTwoFaSms():array {

		$sms_code   = $this->post(\Formatter::TYPE_STRING, "sms_code");
		$two_fa_key = $this->post(\Formatter::TYPE_STRING, "two_fa_key");
		$two_fa_map = Type_Pack_Main::replaceKeyWithMap("two_fa_key", $two_fa_key);

		try {
			Domain_User_Scenario_Api::tryConfirmTwoFaSms($this->user_id, $two_fa_map, $sms_code);
		} catch (cs_TwoFaInvalidUser|cs_WrongTwoFaKey) {

			return $this->error(2302, "2fa key is invalid");
		} catch (cs_WrongCode $e) {

			return $this->error(7, "incorrect code", [
				"available_attempts" => $e->getAvailableAttempts(),
				"next_attempt"       => $e->getNextAttempt(),
			]);
		} catch (cs_TwoFaIsExpired) {

			return $this->error(2300, "2fa key is expired");
		} catch (cs_TwoFaIsFinished) {

			return $this->error(2301, "2fa action already finished");
		} catch (cs_ErrorCountLimitExceeded $e) {

			return $this->error(51, "error count limit", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		}

		// аналитика что смс воспользовались
		Gateway_Bus_CollectorAgent::init()->inc("row70");

		return $this->ok();
	}

	/**
	 * Метод для переотправки смс для 2fa действия
	 *
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_PlatformNotFound
	 * @throws cs_UnknownKeyType
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function tryResendTwoFaSms():array {

		$two_fa_key          = $this->post(\Formatter::TYPE_STRING, "two_fa_key");
		$two_fa_map          = Type_Pack_Main::replaceKeyWithMap("two_fa_key", $two_fa_key);
		$grecaptcha_response = $this->post(\Formatter::TYPE_STRING, "grecaptcha_response", false);

		try {
			$two_fa_story = Domain_User_Scenario_Api::tryResendTwoFaSms($this->user_id, $two_fa_map, $grecaptcha_response);
		} catch (cs_WrongTwoFaKey|cs_TwoFaInvalidUser) {
			return $this->error(2302, "2fa key is invalid");
		} catch (cs_TwoFaIsExpired) {
			return $this->error(2300, "2fa key is expired");
		} catch (cs_TwoFaIsFinished) {
			return $this->error(2301, "2fa action already finished");
		} catch (cs_ResendCodeCountLimitExceeded) {
			return $this->error(55, "resend count limit");
		} catch (cs_ErrorCountLimitExceeded $e) {

			return $this->error(51, "error count limit", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_ResendWillBeAvailableLater $e) {

			return $this->error(52, "resend will be available later", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_RecaptchaIsRequired) {

			Gateway_Bus_CollectorAgent::init()->inc("row77");
			return $this->error(4, "need grecaptcha_response in request");
		} catch (cs_WrongRecaptcha) {
			return $this->error(3, "not valid captcha. Try again");
		} catch (BlockException $e) {

			return $this->error(1004, "2fa blocked", [
				"next_attempt" => $e->getExpire(),
			]);
		}

		return $this->ok(Apiv1_Pivot_Format::twoFaStoryInfo($two_fa_story));
	}
}
