<?php

namespace Compass\Pivot;

/**
 * Действие аутентификации
 *
 * Class Domain_User_Action_Login
 */
class Domain_User_Action_Login {

	/**
	 * действие регстрации
	 *
	 * @param int    $user_id
	 * @param string $phone_number
	 *
	 * @return Struct_User_Auth_Story
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, string $phone_number):Struct_User_Auth_Story {

		$sms_code = self::_getSmsCode($phone_number);

		$locale_config = getConfig("LOCALE_TEXT");

		// формируем текст сообщения
		$sms_text = \BaseFrame\System\Locale::getText($locale_config, "sms_confirm", "auth", \BaseFrame\System\Locale::getLocale(), [
			"sms_code" => addConfirmCodeDash($sms_code),
		]);

		// генерируем sms_id – случайный uuid
		$sms_id = generateUUID();

		// добавляем записи в базу
		$auth_story_data = Domain_User_Entity_StoryHandler_LoginStory::handle($user_id, $phone_number, $sms_code, $sms_id);

		// отправляем задачу в sms сервис
		Type_Sms_Queue::send($phone_number, $sms_text, Type_Sms_Analytics_Story::STORY_TYPE_AUTH, $auth_story_data->auth_phone->auth_map, $sms_id);

		// логируем в аналитику
		Type_Sms_Analytics_Story::onStart(
			$user_id, Type_Sms_Analytics_Story::STORY_TYPE_AUTH, $auth_story_data->auth_phone->auth_map, $auth_story_data->auth->expires_at, $sms_id, $phone_number
		);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// кэшируем код
		Domain_User_Entity_CachedConfirmCode::storeAuthCode($sms_code);

		// добавляем в phphooker задачу, чтобы в случае протухания попытки – скинуть лог в аналитику
		Type_Phphooker_Main::onAuthStoryExpire($auth_story_data->auth_phone->auth_map, $auth_story_data->auth->expires_at);

		return $auth_story_data;
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