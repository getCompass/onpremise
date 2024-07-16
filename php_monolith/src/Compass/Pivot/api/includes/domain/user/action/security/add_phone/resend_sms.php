<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\LocaleTextNotFound;

/**
 * Делаем переотправку смс
 */
class Domain_User_Action_Security_AddPhone_ResendSms {

	/**
	 * Выполняем переотправку
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 * @throws LocaleTextNotFound
	 * @throws \cs_UnpackHasFailed
	 */
	public static function do(Domain_User_Entity_Security_AddPhone_SmsStory $sms_story, Domain_User_Entity_Security_AddPhone_Story $story):array {

		// получаем данные об смс
		$sms_story_data = $sms_story->getSmsStoryData();

		// определяем время следующей переотправки
		$next_resend_at = time() + Domain_User_Entity_Security_AddPhone_SmsStory::NEXT_RESEND_AFTER;
		$resend_count   = $sms_story_data->resend_count + 1;

		if ($resend_count === Domain_User_Entity_Security_AddPhone_SmsStory::MAX_RESEND_COUNT) {
			$next_resend_at = 0;
		}

		// переотправляем смс
		[$sms_code, $sms_id] = self::_resendSms($sms_story, $story);

		// обновляем запись в бд
		$set = [
			"sms_code_hash"  => Type_Hash_Code::makeHash($sms_code),
			"sms_id"         => $sms_id,
			"next_resend_at" => $next_resend_at,
			"resend_count"   => $resend_count,
			"updated_at"     => time(),
		];
		Gateway_Db_PivotPhone_PhoneAddViaSmsStory::set(
			$story->getStoryMap(),
			$sms_story_data->phone_number,
			$set,
		);
		$updated_sms_story = Domain_User_Entity_Security_AddPhone_SmsStory::createFromAnotherSmsStoryData($sms_story_data, $set);

		return [$story, $updated_sms_story];
	}

	/**
	 * Делаем отправку смс
	 *
	 * @throws \queryException
	 * @throws LocaleTextNotFound
	 * @throws \parseException
	 */
	protected static function _resendSms(Domain_User_Entity_Security_AddPhone_SmsStory $sms_story, Domain_User_Entity_Security_AddPhone_Story $story):array {

		try {

			// получаем код из кэша или генерим новый, если кэш пуст
			$sms_code = Domain_User_Entity_CachedConfirmCode::getAddPhoneCode($sms_story->getSmsStoryData()->stage);
		} catch (cs_CacheIsEmpty) {

			$sms_code = generateConfirmCode();
		}
		$locale_config = getConfig("LOCALE_TEXT");

		// формируем текст сообщения
		$sms_text = \BaseFrame\System\Locale::getText($locale_config, "sms_confirm", "add_phone", \BaseFrame\System\Locale::getLocale(), [
			"sms_code" => addConfirmCodeDash($sms_code),
		]);

		// переотправляем смс
		$sms_id = Type_Sms_Queue::resend(
			$sms_story->getSmsStoryData()->phone_number,
			$sms_text,
			$sms_story->getSmsId(),
			Type_Sms_Analytics_Story::STORY_TYPE_PHONE_ADD,
			$story->getStoryMap(),
		);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// пишем в аналитику факт переотправки по запросу пользователя
		Type_Sms_Analytics_Story::onResend($story->getStoryData()->user_id, Type_Sms_Analytics_Story::STORY_TYPE_PHONE_ADD, $story->getStoryMap(),
			$story->getExpiresAt(), $sms_id, $sms_story->getSmsStoryData()->phone_number);

		// сохраняем код смс в кэше
		Domain_User_Entity_CachedConfirmCode::storeAddPhoneCode($sms_code, $sms_story->getSmsStoryData()->stage);

		return [$sms_code, $sms_id];
	}
}