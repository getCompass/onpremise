<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\CountryNotFound;
use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии для работы с безопасностью через номер телефона пользователя
 */
class Domain_User_Scenario_Api_Security_Phone {

	/**
	 * Метод добавления номера телефона
	 *
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_Security_Phone_AlreadySet
	 * @throws Domain_User_Exception_Security_Phone_AlreadyTaken
	 * @throws Domain_User_Exception_Security_Phone_SmsErrorCountExceeded
	 * @throws InvalidPhoneNumber
	 * @throws LocaleTextNotFound
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_blockException
	 * @throws Domain_User_Exception_Security_UserWasRegisteredBySso
	 */
	public static function add(int $user_id, string $session_uniq, string $phone_number):array {

		// проверяем что добавление номера телефона не отключено в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER);

		// валидируем номер телефона
		$phone_number = (new \BaseFrame\System\PhoneNumber($phone_number))->number();

		// проверяем что пользователь не зарегистрирован через SSO
		Domain_User_Entity_Phone::assertUserWasNotRegisteredBySso($user_id);

		// проверяем что у пользователя не привязан номер телефона
		Domain_User_Entity_Validator::assertUserHaveNotPhone($user_id);

		// проверяем что номер ни за кем не закреплен
		Domain_User_Entity_Validator::assertPhoneIsNotTaken($phone_number);

		// проверяем что нет уже начатого процесса добавления номер телефона
		try {

			$story = Domain_User_Entity_Security_AddPhone_Story::getFromSessionCache($phone_number)
				->assertNotExpire()
				->assertNotSuccess();

			try {

				// получаем запись об смс для этой смены номера и проверяем, актуальна ли
				$sms_story = $story->getActiveSmsStoryEntity()->assertErrorCountNotExceeded();
			} catch (Domain_User_Exception_Security_Phone_SmsErrorCountExceeded $e) {

				// выкидываем ошибку о том, что смена номера временно заблокирована (из-за превышения кол-ва ошибок)
				$e->setNextAttempt($story->getExpiresAt());
				throw $e;
			}
		} catch (cs_CacheIsEmpty|Domain_User_Exception_Security_Phone_IsExpired|Domain_User_Exception_Security_Phone_IsSuccess|Domain_User_Exception_Security_Phone_SmsNotFound) {

			// выполняем начало смены номера телефона и сохраняем в кэше
			/** @var Domain_User_Entity_Security_AddPhone_Story $story */
			[$story, $sms_story] = Domain_User_Action_Security_AddPhone_FirstStage::do($user_id, $session_uniq, $phone_number);
			$story->storeInSessionCache($phone_number);

			// добавляем в phphooker задачу, чтобы в случае протухания попытки – скинуть лог в аналитику
			Type_Phphooker_Main::onPhoneAddStoryExpire($user_id, $story->getStoryMap(), $story->getExpiresAt());
		}

		return [$story, $sms_story];
	}

	/**
	 * Метод добавления номера телефона
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws Domain_User_Exception_Security_Phone_AlreadySet
	 * @throws Domain_User_Exception_Security_Phone_AlreadyTaken
	 * @throws Domain_User_Exception_Security_Phone_IsExpired
	 * @throws Domain_User_Exception_Security_Phone_IsSuccess
	 * @throws Domain_User_Exception_Security_Phone_SmsErrorCountExceeded
	 * @throws Domain_User_Exception_Security_Phone_SmsNotFound
	 * @throws Domain_User_Exception_Security_Phone_StoryWrongMap
	 * @throws InvalidPhoneNumber
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 * @throws cs_WrongCode
	 * @throws cs_blockException
	 */
	public static function confirmAddition(int $user_id, string $sms_code, string $add_phone_story_map):string {

		// проверяем что добавление номера телефона не отключено в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER);

		$story = Domain_User_Entity_Security_AddPhone_Story::getByMap($user_id, $add_phone_story_map)
			->assertUserAuthorized($user_id)
			->assertNotExpire()
			->assertNotSuccess();

		// получаем запись об смс
		$sms_story = $story->getActiveSmsStoryEntity();

		try {

			// проверяем что не сработал лимит и что пришел верный код
			$sms_story->assertErrorCountNotExceeded();
			$sms_story->assertEqualSmsCode($sms_code);

			// проверяем что у пользователя не привязан номер телефона
			Domain_User_Entity_Validator::assertUserHaveNotPhone($user_id);

			// проверяем что номер ни за кем не закреплен
			Domain_User_Entity_Validator::assertPhoneIsNotTaken($sms_story->getPhoneNumber());

			// проверяем блокировку
			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::PHONE_CONFIRM_ADDITION);

			// добавляем номер
			Domain_User_Action_Security_AddPhone_ConfirmFirstStage::do($user_id, $sms_story, $story);

			// фиксируем в аналитике, что пользователь использовал код из смс
			Type_Sms_Analytics_Story::onConfirm($user_id, Type_Sms_Analytics_Story::STORY_TYPE_PHONE_ADD, $add_phone_story_map,
				$story->getExpiresAt(), $sms_story->getSmsId(), $sms_story->getPhoneNumber());
		} catch (Domain_User_Exception_Security_Phone_SmsErrorCountExceeded $e) {

			// выкидываем ошибку о том, что смена номера временно заблокирована (из-за превышения кол-ва ошибок)
			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		} catch (cs_WrongCode) {

			// увеличиваем счетчик, и если не осталось попыток, выкидываем блокировку
			$available_attempts = Domain_User_Action_Security_AddPhone_IncrementError::do($sms_story, $story);
			if ($available_attempts === 0) {
				throw new Domain_User_Exception_Security_Phone_SmsErrorCountExceeded($story->getExpiresAt());
			}
			throw new cs_WrongCode($available_attempts, $story->getExpiresAt());
		}

		return $sms_story->getSmsStoryData()->phone_number;
	}

	/**
	 * Метод добавления номера телефона
	 *
	 * @throws CountryNotFound
	 * @throws Domain_User_Exception_Security_Phone_IsExpired
	 * @throws Domain_User_Exception_Security_Phone_IsSuccess
	 * @throws Domain_User_Exception_Security_Phone_SmsErrorCountExceeded
	 * @throws Domain_User_Exception_Security_Phone_SmsNotFound
	 * @throws Domain_User_Exception_Security_Phone_SmsResendCountExceeded
	 * @throws Domain_User_Exception_Security_Phone_SmsResendNotAvailable
	 * @throws Domain_User_Exception_Security_Phone_StoryWrongMap
	 * @throws InvalidPhoneNumber
	 * @throws LocaleTextNotFound
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \userAccessException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_PhoneChangeIsExpired
	 * @throws cs_PhoneChangeIsSuccess
	 * @throws cs_PhoneChangeSmsErrorCountExceeded
	 * @throws cs_PhoneChangeSmsNotFound
	 * @throws cs_PhoneChangeSmsResendCountExceeded
	 * @throws cs_PhoneChangeSmsResendNotAvailable
	 * @throws cs_PhoneChangeStoryWrongMap
	 */
	public static function resendSms(int $user_id, string $phone_story_key, string $phone_story_type):array {

		switch ($phone_story_type) {

			case Domain_User_Entity_Security_AddPhone_Story::ACTION_TYPE:

				try {
					$add_phone_story_map = Type_Pack_AddPhoneStory::doDecrypt($phone_story_key);
				} catch (\cs_DecryptHasFailed) {
					throw new ParamException("invalid add_phone_story_key");
				}

				return self::_resendSmsForNumberAdd($user_id, $add_phone_story_map);

			case Domain_User_Entity_ChangePhone_Story::ACTION_TYPE:

				try {
					$change_phone_story_map = Type_Pack_ChangePhoneStory::doDecrypt($phone_story_key);
				} catch (\cs_DecryptHasFailed) {
					throw new ParamException("invalid change_phone_story_key");
				}

				return Domain_User_Scenario_Api::resendSmsForNumberChange($user_id, $change_phone_story_map);

			default:

				throw new ParamException("invalid phone_story_type");
		}
	}

	/**
	 * Переотправляем смс для добавления номера
	 *
	 * @throws Domain_User_Exception_Security_Phone_IsExpired
	 * @throws Domain_User_Exception_Security_Phone_IsSuccess
	 * @throws Domain_User_Exception_Security_Phone_SmsErrorCountExceeded
	 * @throws Domain_User_Exception_Security_Phone_SmsNotFound
	 * @throws Domain_User_Exception_Security_Phone_SmsResendCountExceeded
	 * @throws Domain_User_Exception_Security_Phone_SmsResendNotAvailable
	 * @throws Domain_User_Exception_Security_Phone_StoryWrongMap
	 * @throws InvalidPhoneNumber
	 * @throws LocaleTextNotFound
	 * @throws CountryNotFound
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 */
	protected static function _resendSmsForNumberAdd(int $user_id, string $add_phone_story_map):array {

		$story = Domain_User_Entity_Security_AddPhone_Story::getByMap($user_id, $add_phone_story_map)
			->assertUserAuthorized($user_id)
			->assertNotExpire()
			->assertNotSuccess();

		try {

			$sms_story = $story->getActiveSmsStoryEntity()
				->assertErrorCountNotExceeded()
				->assertResendCountNotExceeded()
				->assertResendIsAvailable();
		} catch (Domain_User_Exception_Security_Phone_SmsErrorCountExceeded $e) {

			// выкидываем ошибку о том, что смена номера временно заблокирована (из-за превышения кол-ва ошибок)
			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		}

		[$story, $updated_sms_story] = Domain_User_Action_Security_AddPhone_ResendSms::do($sms_story, $story);
		$phone_number_obj = new \BaseFrame\System\PhoneNumber($updated_sms_story->getSmsStoryData()->phone_number);

		Type_Phphooker_Main::onSmsResent(
			$user_id,
			$phone_number_obj->obfuscate(),
			Domain_User_Entity_Security_AddPhone_SmsStory::MAX_RESEND_COUNT - $updated_sms_story->getSmsStoryData()->resend_count,
			"add_phone",
			\BaseFrame\Conf\Country::get($phone_number_obj->countryCode())->getLocalizedName(),
			$sms_story->getSmsStoryData()->sms_id
		);

		return [$story, $updated_sms_story];
	}
}