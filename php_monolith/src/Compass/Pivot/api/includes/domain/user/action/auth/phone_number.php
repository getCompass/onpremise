<?php

namespace Compass\Pivot;

/**
 * класс описывает все действия связанные с аутентификацией по номеру телефона
 * @package Compass\Pivot
 */
class Domain_User_Action_Auth_PhoneNumber {

	/**
	 * Получаем пользователя по номеру
	 *
	 * @return int
	 */
	public static function resolveUser(string $phone_number):int {

		$user_id = 0;

		try {

			$phone_uniq = Domain_User_Entity_Phone::getUserPhone($phone_number);
			$user_id    = $phone_uniq->user_id;
		} catch (cs_PhoneNumberNotFound) {
		}

		return $user_id;
	}

	/**
	 * Начинаем регистрацию
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function beginRegistration(string $phone_number):Domain_User_Entity_AuthStory {

		// генерируем код подтерждения и текст смс сообщения
		$confirm_code = self::_getSmsCode($phone_number);
		$sms_text     = self::_getSmsText($confirm_code);

		// генерируем sms_id – случайный uuid
		$sms_id = generateUUID();

		// создаем все необходимые сущности аутентификации
		$expires_at       = time() + Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::STORY_LIFE_TIME;
		$auth_method_data = Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::prepareAuthMethodDataDraft(
			$phone_number, Type_Hash_Code::makeHash($confirm_code), $sms_id
		);
		$auth_story       = Domain_User_Entity_AuthStory::create(0, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER, $expires_at, $auth_method_data);

		// отправляем задачу на отправку sms
		Type_Sms_Queue::send($phone_number, $sms_text, Type_Sms_Analytics_Story::STORY_TYPE_AUTH, $auth_story->getAuthInfo()->auth_map, $sms_id);

		// логируем в аналитику
		Type_Sms_Analytics_Story::onStart(
			0, Type_Sms_Analytics_Story::STORY_TYPE_AUTH, $auth_story->getAuthInfo()->auth_map, $auth_story->getExpiresAt(), $sms_id, $phone_number
		);

		// логируем что отправили смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// кэшируем код
		Domain_User_Entity_CachedConfirmCode::storeAuthCode($confirm_code, $auth_story->getLifeTime());

		// добавляем в phphooker задачу, чтобы в случае протухания попытки – скинуть лог в аналитику
		Type_Phphooker_Main::onAuthStoryExpire($auth_story->getAuthMap(), $auth_story->getExpiresAt());

		return $auth_story;
	}

	/**
	 * Начинаем логин
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function beginLogin(int $user_id, string $phone_number):Domain_User_Entity_AuthStory {

		// генерируем код подтерждения и текст смс сообщения
		$confirm_code = self::_getSmsCode($phone_number);
		$sms_text     = self::_getSmsText($confirm_code);

		// генерируем sms_id – случайный uuid
		$sms_id = generateUUID();

		// создаем все необходимые сущности аутентификации
		$expires_at       = time() + Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::STORY_LIFE_TIME;
		$auth_method_data = Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::prepareAuthMethodDataDraft(
			$phone_number, Type_Hash_Code::makeHash($confirm_code), $sms_id
		);
		$auth_story       = Domain_User_Entity_AuthStory::create($user_id, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_PHONE_NUMBER, $expires_at, $auth_method_data);

		// отправляем задачу в sms сервис
		Type_Sms_Queue::send($phone_number, $sms_text, Type_Sms_Analytics_Story::STORY_TYPE_AUTH, $auth_story->getAuthInfo()->auth_map, $sms_id);

		// логируем в аналитику
		Type_Sms_Analytics_Story::onStart(
			$user_id, Type_Sms_Analytics_Story::STORY_TYPE_AUTH, $auth_story->getAuthInfo()->auth_map, $auth_story->getExpiresAt(), $sms_id, $phone_number
		);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// кэшируем код
		Domain_User_Entity_CachedConfirmCode::storeAuthCode($confirm_code, $auth_story->getLifeTime());

		// добавляем в phphooker задачу, чтобы в случае протухания попытки – скинуть лог в аналитику
		Type_Phphooker_Main::onAuthStoryExpire($auth_story->getAuthMap(), $auth_story->getExpiresAt());

		return $auth_story;
	}

	/**
	 * Переотправляем код подтверждения
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function resend(Domain_User_Entity_AuthStory $auth_story):Domain_User_Entity_AuthStory {

		// получаем текущий проверочный код
		try {
			$confirm_code = Domain_User_Entity_CachedConfirmCode::getAuthCode();
		} catch (cs_CacheIsEmpty) {

			// если в кэше не нашли проверочный код – логируем и генерируем новый
			Type_System_Admin::log("auth_story_resend", "не нашли проверочный код в кэше для {$auth_story->getAuthMap()}");
			$confirm_code = generateConfirmCode();
		}

		$sms_text = self::_getSmsText($confirm_code);

		// отправляем задачу в sms сервис
		$new_sms_id = Type_Sms_Queue::resend(
			$auth_story->getAuthPhoneHandler()->getPhoneNumber(), $sms_text, $auth_story->getAuthPhoneHandler()->getSmsID(), Type_Sms_Analytics_Story::STORY_TYPE_AUTH, $auth_story->getAuthMap()
		);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// пишем в аналитику факт переотправки по запросу пользователя
		Type_Sms_Analytics_Story::onResend(
			$auth_story->getUserId(),
			Type_Sms_Analytics_Story::STORY_TYPE_AUTH,
			$auth_story->getAuthMap(),
			$auth_story->getExpiresAt(),
			$new_sms_id,
			$auth_story->getAuthPhoneHandler()->getPhoneNumber()
		);

		$auth_story->getAuthPhoneHandler()->handleResend($confirm_code, [
			"sms_id" => $new_sms_id,
		]);

		return $auth_story;
	}

	/**
	 * Получаем текст смс для начала аутентификации
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 */
	protected static function _getSmsText(string $sms_code):string {

		$locale_config = getConfig("LOCALE_TEXT");

		// формируем текст сообщения
		return \BaseFrame\System\Locale::getText($locale_config, "sms_confirm", "auth", \BaseFrame\System\Locale::getLocale(), [
			"sms_code" => addConfirmCodeDash($sms_code),
		]);
	}

	/**
	 * Получить sms код
	 *
	 * @param string $phone_number
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected static function _getSmsCode(string $phone_number):string {

		// генерируем данные для смс
		$sms_code = generateConfirmCode();

		if ($phone_number == IOS_TEST_PHONE) {
			$sms_code = IOS_TEST_SMS_CODE;
		}
		if ($phone_number == IOS_TEST_PHONE2) {
			$sms_code = IOS_TEST_SMS_CODE2;
		}
		if ($phone_number == IOS_TEST_PHONE3) {
			$sms_code = IOS_TEST_SMS_CODE3;
		}
		if ($phone_number == IOS_TEST_PHONE4) {
			$sms_code = IOS_TEST_SMS_CODE4;
		}
		if ($phone_number == ELECTRON_TEST_PHONE) {
			$sms_code = ELECTRON_TEST_SMS_CODE;
		}
		if ($phone_number == ANDROID_TEST_PHONE) {
			$sms_code = ANDROID_TEST_SMS_CODE;
		}

		return $sms_code;
	}
}