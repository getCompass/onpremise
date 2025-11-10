<?php

namespace Compass\Pivot;

/**
 * Действие отправки смс для подтверждения 2fa действия
 *
 * Class Domain_User_Action_TwoFa_SendSms
 */
class Domain_User_Action_TwoFa_SendSms {

	/**
	 * действие отправки
	 *
	 * @param int                       $user_id
	 * @param Struct_Db_PivotAuth_TwoFa $two_fa
	 *
	 * @return Struct_Db_PivotAuth_TwoFaPhone
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_UserPhoneSecurityNotFound
	 * @throws \queryException
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function do(int $user_id, Struct_Db_PivotAuth_TwoFa $two_fa):Struct_Db_PivotAuth_TwoFaPhone {

		// получаем user_security
		$phone_number = Domain_User_Entity_Phone::getPhoneByUserId($user_id);

		// генерируем данные для смс
		$sms_code = generateConfirmCode();

		// получаем фиксироавнный проверочный код для тестовых номеров телефона
		$sms_code = self::_getFixedSmsCodeForTestPhoneNumbers($sms_code, $phone_number);

		// получим группу с которой будем брать текст по типу токена
		$key           = Domain_User_Entity_Confirmation_Main::getGroupNameByActionType($two_fa->action_type);
		$locale_config = getConfig("LOCALE_TEXT");

		// формируем текст сообщения
		$sms_text = \BaseFrame\System\Locale::getText($locale_config, "sms_confirm", $key, \BaseFrame\System\Locale::getLocale(), [
			"sms_code" => addConfirmCodeDash($sms_code),
		]);

		// генерируем sms_id – случайный uuid
		$sms_id = generateUUID();

		// добавляем запись в базу о two_fa попытке
		$time         = time();
		$next_attempt = $time + Domain_User_Entity_Confirmation_TwoFa_Story::NEXT_ATTEMPT_AFTER;
		$two_fa_phone = self::_makeTwoFaPhoneStruct($two_fa, $time, $next_attempt, $sms_id, $sms_code, $phone_number);

		// отправляем задачу в sms сервис
		Type_Sms_Queue::send($phone_number, $sms_text, Type_Sms_Analytics_Story::STORY_TYPE_TWO_FA, $two_fa_phone->two_fa_map, $sms_id);

		// логируем в аналитику
		Type_Sms_Analytics_Story::onStart(
			$user_id, Type_Sms_Analytics_Story::STORY_TYPE_TWO_FA, $two_fa_phone->two_fa_map, $two_fa->expires_at, $sms_id, $phone_number
		);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		Gateway_Db_PivotAuth_TwoFaPhoneList::insert($two_fa_phone);

		// кэшируем код
		Domain_User_Entity_CachedConfirmCode::storeAuthCode($sms_code, Domain_User_Entity_Confirmation_TwoFa_TwoFa::EXPIRE_AT);

		return $two_fa_phone;
	}

	/**
	 * получаем проверочный код для тестовых номеров телефона
	 *
	 * @return string
	 */
	protected static function _getFixedSmsCodeForTestPhoneNumbers(string $generated_sms_code, string $phone_number):string {

		//
		if ($phone_number == IOS_TEST_PHONE) {
			return IOS_TEST_SMS_CODE;
		}
		if ($phone_number == IOS_TEST_PHONE2) {
			return IOS_TEST_SMS_CODE2;
		}
		if ($phone_number == IOS_TEST_PHONE3) {
			return IOS_TEST_SMS_CODE3;
		}
		if ($phone_number == IOS_TEST_PHONE4) {
			return IOS_TEST_SMS_CODE4;
		}
		if ($phone_number == ELECTRON_TEST_PHONE) {
			return ELECTRON_TEST_SMS_CODE;
		}
		if ($phone_number == ANDROID_TEST_PHONE) {
			return ANDROID_TEST_SMS_CODE;
		}
		if ($phone_number == ANDROID_TEST_PHONE2) {
			return ANDROID_TEST_SMS_CODE2;
		}

		// если дошли до сюда, то номер телефона не тестовый, а значит возвращаем сгенерированный код
		return $generated_sms_code;
	}

	/**
	 * собираем структуру Struct_Db_PivotAuth_TwoFaPhone
	 *
	 * @return Struct_Db_PivotAuth_TwoFaPhone
	 * @throws cs_IncorrectSaltVersion
	 */
	protected static function _makeTwoFaPhoneStruct(Struct_Db_PivotAuth_TwoFa $two_fa,
									int                       $time,
									int                       $next_attempt,
									string                    $sms_id,
									string                    $sms_code,
									string                    $phone_number):Struct_Db_PivotAuth_TwoFaPhone {

		return new Struct_Db_PivotAuth_TwoFaPhone(
			$two_fa->two_fa_map,
			0,
			0,
			0,
			$time,
			$time,
			$next_attempt,
			$sms_id,
			Type_Hash_Code::makeHash($sms_code),
			$phone_number
		);
	}
}
