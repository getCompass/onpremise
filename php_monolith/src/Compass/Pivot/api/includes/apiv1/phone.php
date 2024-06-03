<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Restrictions\Exception\ActionRestrictedException;

/**
 * Методы для работы с номером телефона
 */
class Apiv1_Phone extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"tryChangePhoneStep1",
		"tryConfirmSms",
		"tryChangePhoneStep2",
		"doResendSms",
		"getPhoneData",
	];

	/**
	 * Начинаем смену номера телефон
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws ParamException
	 */
	public function tryChangePhoneStep1():array {

		try {
			[$story, $sms_story] = Domain_User_Scenario_Api::changePhoneStep1($this->user_id, $this->session_uniq);
		} catch (BlockException $e) {

			return $this->error(1300, "change phone blocked", [
				"next_attempt" => $e->getExpire(),
			]);
		} catch (cs_PhoneChangeSmsErrorCountExceeded $e) {

			return $this->error(1306, "change phone sms error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_UserPhoneSecurityNotFound) {
			throw new ParamException("not found phone for user");
		} catch (ActionRestrictedException) {
			return $this->error(855, "action is restricted");
		}

		return $this->ok(Apiv1_Format::changePhoneProcessStage1($story, $sms_story));
	}

	/**
	 * Подтверждение смс
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function tryConfirmSms():array {

		$change_phone_story_key = $this->post(\Formatter::TYPE_STRING, "change_phone_story_key");
		$sms_code               = $this->post(\Formatter::TYPE_STRING, "sms_code");

		try {
			$change_phone_story_map = Type_Pack_ChangePhoneStory::doDecrypt($change_phone_story_key);
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("invalid change_phone_story_key");
		}

		try {
			Domain_User_Scenario_Api::confirmSmsForChangePhone($this->user_id, $change_phone_story_map, $sms_code);
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("not found code");
		} catch (cs_PhoneChangeIsExpired|cs_PhoneChangeIsSuccess|cs_PhoneChangeSmsNotFound|cs_PhoneChangeStoryWrongMap) {
			return $this->error(1301, "change phone active process not found");
		} catch (cs_WrongCode $e) {

			return $this->error(1302, "wrong sms code", [
				"available_attempts" => $e->getAvailableAttempts(),
			]);
		} catch (BlockException $e) {

			return $this->error(1300, "change phone blocked", [
				"next_attempt" => $e->getExpire(),
			]);
		} catch (cs_PhoneChangeSmsErrorCountExceeded $e) {

			return $this->error(1306, "change phone sms error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_UserPhoneSecurityNotFound) {
			throw new ParamException("not found phone for user");
		}

		return $this->ok();
	}

	/**
	 * Вводим новый номер телефона для смены
	 *
	 * @return array
	 * @throws ParamException
	 * @throws cs_IncorrectSaltVersion
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \userAccessException
	 */
	public function tryChangePhoneStep2():array {

		$change_phone_story_key = $this->post(\Formatter::TYPE_STRING, "change_phone_story_key");
		$phone_number           = $this->post(\Formatter::TYPE_STRING, "phone_number");

		try {
			$change_phone_story_map = Type_Pack_ChangePhoneStory::doDecrypt($change_phone_story_key);
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("invalid change_phone_story_key");
		}

		try {

			/** @var Domain_User_Entity_ChangePhone_SmsStory $sms_story */
			[, $sms_story] = Domain_User_Scenario_Api::changePhoneStep2($this->user_id, $change_phone_story_map, $phone_number);
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("not found code");
		} catch (cs_PhoneChangeIsExpired|cs_PhoneChangeIsSuccess|cs_PhoneChangeStoryWrongMap|cs_PhoneChangeWrongStage) {
			return $this->error(1301, "change phone active process not found");
		} catch (\BaseFrame\Exception\Domain\InvalidPhoneNumber) {
			throw new ParamException("invalid phone number format");
		} catch (cs_PhoneAlreadyAssignedToCurrentUser) {
			return $this->error(1305, "phone is equal to old phone number");
		} catch (cs_PhoneAlreadyRegistered) {
			return $this->error(1304, "phone number already registered in compass");
		} catch (BlockException $e) {

			return $this->error(1300, "change phone blocked", [
				"next_attempt" => $e->getExpire(),
			]);
		} catch (cs_PhoneChangeSmsErrorCountExceeded $e) {

			return $this->error(1306, "change phone sms error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		}

		return $this->ok(Apiv1_Format::changePhoneProcessStage2($sms_story));
	}

	/**
	 * Переотправка смс при смене номера
	 *
	 * @return array
	 * @throws ParamException
	 * @throws cs_IncorrectSaltVersion
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \userAccessException
	 */
	public function doResendSms():array {

		$change_phone_story_key = $this->post(\Formatter::TYPE_STRING, "change_phone_story_key");

		try {
			$change_phone_story_map = Type_Pack_ChangePhoneStory::doDecrypt($change_phone_story_key);
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("invalid change_phone_story_key");
		}

		try {
			[, $sms_story] = Domain_User_Scenario_Api::resendSmsForNumberChange($this->user_id, $change_phone_story_map);
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("not found code");
		} catch (cs_PhoneChangeIsExpired|cs_PhoneChangeIsSuccess|cs_PhoneChangeStoryWrongMap|cs_PhoneChangeWrongStage|cs_PhoneChangeSmsNotFound) {
			return $this->error(1301, "change phone active process not found");
		} catch (cs_PhoneChangeSmsResendNotAvailable $e) {

			return $this->error(1303, "resend is not available", [
				"next_resend" => $e->getNextAttempt(),
			]);
		} catch (cs_PhoneChangeSmsResendCountExceeded) {

			return $this->error(1303, "resend is not available", [
				"next_resend" => 0,
			]);
		} catch (BlockException $e) {

			return $this->error(1300, "change phone blocked", [
				"next_attempt" => $e->getExpire(),
			]);
		} catch (cs_PhoneChangeSmsErrorCountExceeded $e) {

			return $this->error(1306, "change phone sms error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		}

		return $this->ok(Apiv1_Format::changePhoneResendSms($sms_story));
	}

	/**
	 * Получить данные о номере телефона
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getPhoneData():array {

		try {
			$phone_number_obj = Domain_User_Scenario_Api::getPhoneNumberInfo($this->user_id);
		} catch (cs_UserPhoneSecurityNotFound) {
			throw new ParamException("not found phone for user");
		} catch (\BaseFrame\Exception\Domain\InvalidPhoneNumber) {
			throw new ParamException("invalid phone number");
		}

		return $this->ok(Apiv1_Format::phoneNumberData($phone_number_obj));
	}
}