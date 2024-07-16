<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\CountryNotFound;
use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Class Apiv2_Security_Phone
 */
class Apiv2_Security_Phone extends \BaseFrame\Controller\Api {

	public const ECODE_AUTHORIZATION_PHONE_DISABLED = 1216001; // отключена на сервере авторизация по телефону в конфиге
	public const ECODE_PHONE_ALREADY_SET            = 1216002; // у пользователя уже установлен номер телефона
	public const ECODE_PHONE_ALREADY_TAKEN          = 1216003; // номер уже занят
	public const ECODE_INCORRECT_CONFIRM_CODE       = 1216004; // неверный код подтверждения
	public const ECODE_CONFIRM_CODE_EXPIRED         = 1216012; // время выполнение процесса истекло 20min нужно начать процесс снова
	public const ECODE_PHONE_BLOCK_EXCEPTION        = 1216013; // поймали блокировку попыток добавить номер телефона
	public const ECODE_SMS_COUNT_EXCEPTION          = 1216014; // поймали блокировку попыток отправить sms
	public const ECODE_NO_ACTIVE_PROCESS            = 1216015; // нет активного процесса добавления
	public const ECODE_RESEND_NOT_AVAILABLE         = 1216016; // пересылка смс недоступна
	public const ECODE_USER_WAS_REGISTERED_BY_SSO   = 1216017; // пользователь был зарегистрирован через SSO, действие запрещено

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"add",
		"confirmAddition",
		"resendSms",
	];

	/**
	 * Метод добавления номера телефона (начало процесса)
	 *
	 * @throws CaseException
	 * @throws LocaleTextNotFound
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 */
	public function add():array {

		$phone_number = $this->post(\Formatter::TYPE_STRING, "phone_number");

		try {
			// проверяем блокировку
			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PHONE_ADD);

			[$story, $sms_story] = Domain_User_Scenario_Api_Security_Phone::add($this->user_id, $this->session_uniq, $phone_number);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(self::ECODE_AUTHORIZATION_PHONE_DISABLED, "authorization phone disabled");
		} catch (Domain_User_Exception_Security_Phone_AlreadySet) {
			throw new CaseException(self::ECODE_PHONE_ALREADY_SET, "phone number already set");
		} catch (Domain_User_Exception_Security_Phone_AlreadyTaken) {
			throw new CaseException(self::ECODE_PHONE_ALREADY_TAKEN, "phone number already taken");
		} catch (BlockException $e) {

			throw new CaseException(self::ECODE_PHONE_BLOCK_EXCEPTION, "add phone blocked", [
				"next_attempt" => $e->getExpire(),
			]);
		} catch (Domain_User_Exception_Security_Phone_SmsErrorCountExceeded $e) {

			throw new CaseException(self::ECODE_SMS_COUNT_EXCEPTION, "add phone sms error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (InvalidPhoneNumber) {
			throw new ParamException("incorrect phone number");
		} catch (Domain_User_Exception_Security_UserWasRegisteredBySso) {
			throw new CaseException(static::ECODE_USER_WAS_REGISTERED_BY_SSO, "user was registered by sso, action is not allowed");
		}

		return $this->ok(Apiv2_Format::addPhone($story, $sms_story));
	}

	/**
	 * Метод подтверждение добавления телефона проверочным кодом из смс (завершение процесса)
	 *
	 * @throws BusFatalException
	 * @throws CaseException
	 * @throws InvalidPhoneNumber
	 * @throws ParamException
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 */
	public function confirmAddition():array {

		$sms_code            = $this->post(\Formatter::TYPE_STRING, "code");
		$add_phone_story_key = $this->post(\Formatter::TYPE_STRING, "add_phone_story_key");

		try {
			$add_phone_story_map = Type_Pack_AddPhoneStory::doDecrypt($add_phone_story_key);
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("invalid add_phone_story_key");
		}

		try {
			$phone = Domain_User_Scenario_Api_Security_Phone::confirmAddition($this->user_id, $sms_code, $add_phone_story_map);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(self::ECODE_AUTHORIZATION_PHONE_DISABLED, "authorization phone disabled");
		} catch (Domain_User_Exception_Security_Phone_StoryWrongMap|Domain_User_Exception_Security_Phone_SmsNotFound|Domain_User_Exception_Security_Phone_IsSuccess) {
			throw new CaseException(self::ECODE_NO_ACTIVE_PROCESS, "add phone active process not found");
		} catch (Domain_User_Exception_Security_Phone_AlreadySet) {
			throw new CaseException(self::ECODE_PHONE_ALREADY_SET, "phone number already set");
		} catch (Domain_User_Exception_Security_Phone_AlreadyTaken|Domain_User_Exception_PhoneNumberBinding) {
			throw new CaseException(self::ECODE_PHONE_ALREADY_TAKEN, "phone number already taken");
		} catch (cs_WrongCode $e) {
			throw new CaseException(self::ECODE_INCORRECT_CONFIRM_CODE, "incorrect confirm code", [
				"code_available_attempts" => $e->getAvailableAttempts(),
				"next_attempt"            => $e->getNextAttempt(),
			]);
		} catch (Domain_User_Exception_Security_Phone_IsExpired) {
			throw new CaseException(self::ECODE_CONFIRM_CODE_EXPIRED, "time to confirm code expired");
		} catch (BlockException $e) {

			throw new CaseException(self::ECODE_PHONE_BLOCK_EXCEPTION, "phone blocked", [
				"next_attempt" => $e->getExpire(),
			]);
		} catch (Domain_User_Exception_Security_Phone_SmsErrorCountExceeded $e) {

			throw new CaseException(self::ECODE_SMS_COUNT_EXCEPTION, "add phone sms error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("user not authorized");
		}

		return $this->ok([
			"phone_mask" => (string) Domain_User_Entity_Phone::getPhoneNumberMask($phone),
		]);
	}

	/**
	 * Метод переотправки проверочного кода по sms процесса действия над телефоном для авторизованного пользователя
	 *
	 * @return array
	 * @throws CaseException
	 * @throws CountryNotFound
	 * @throws InvalidPhoneNumber
	 * @throws LocaleTextNotFound
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \userAccessException
	 * @throws cs_IncorrectSaltVersion
	 */
	public function resendSms():array {

		$phone_story_key  = $this->post(\Formatter::TYPE_STRING, "phone_story_key");
		$phone_story_type = $this->post(\Formatter::TYPE_STRING, "phone_story_type");

		try {
			[$story, $sms_story] = Domain_User_Scenario_Api_Security_Phone::resendSms($this->user_id, $phone_story_key, $phone_story_type);
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("not found code");
		} catch (cs_PhoneChangeIsExpired|cs_PhoneChangeIsSuccess|cs_PhoneChangeStoryWrongMap|cs_PhoneChangeWrongStage|cs_PhoneChangeSmsNotFound|
		Domain_User_Exception_Security_Phone_StoryWrongMap|Domain_User_Exception_Security_Phone_SmsNotFound|
		Domain_User_Exception_Security_Phone_IsSuccess|Domain_User_Exception_Security_Phone_IsExpired) {
			throw new CaseException(self::ECODE_NO_ACTIVE_PROCESS, "add phone active process not found");
		} catch (cs_PhoneChangeSmsResendCountExceeded|Domain_User_Exception_Security_Phone_SmsResendCountExceeded) {

			throw new CaseException(self::ECODE_RESEND_NOT_AVAILABLE, "resend is not available", [
				"next_resend" => 0,
			]);
		} catch (cs_PhoneChangeSmsResendNotAvailable|Domain_User_Exception_Security_Phone_SmsResendNotAvailable $e) {

			throw new CaseException(self::ECODE_RESEND_NOT_AVAILABLE, "resend is not available", [
				"next_resend" => $e->getNextAttempt(),
			]);
		} catch (BlockException $e) {

			throw new CaseException(self::ECODE_PHONE_BLOCK_EXCEPTION, "phone blocked", [
				"next_attempt" => $e->getExpire(),
			]);
		} catch (cs_PhoneChangeSmsErrorCountExceeded|Domain_User_Exception_Security_Phone_SmsErrorCountExceeded $e) {

			throw new CaseException(self::ECODE_SMS_COUNT_EXCEPTION, "phone sms error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		}

		return $this->ok(Apiv2_Format::resendSms($story, $sms_story, $phone_story_type));
	}
}