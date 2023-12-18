<?php

namespace Compass\Pivot;

/**
 * Второй этап смены номера телефона
 */
class Domain_User_Action_ChangePhone_SecondStage {

	/**
	 * Выполняем отправку смс на новый номер
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(
		Domain_User_Entity_ChangePhone_Story     $story,
		string                                   $phone_number,
		?Domain_User_Entity_ChangePhone_SmsStory $old_sms_story = null
	):array {

		$change_phone_story_map = $story->getStoryMap();

		[$sms_code, $sms_story] = self::_doCreateSms($story, $phone_number, $change_phone_story_map);

		Gateway_Db_PivotPhone_PhoneChangeViaSmsStory::insert($sms_story->getSmsStoryData());

		// блокируем прошлое смс
		if ($old_sms_story !== null) {

			self::doBlockOldSms($change_phone_story_map, $old_sms_story);
		}

		// сохраняем код смс в кэше
		Domain_User_Entity_CachedConfirmCode::storeChangePhoneCode($sms_code, Domain_User_Entity_ChangePhone_Story::STAGE_SECOND);

		return [$story, $sms_story];
	}

	/**
	 * Блокируем прошлое смс
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	protected static function doBlockOldSms(string $change_phone_story_map, Domain_User_Entity_ChangePhone_SmsStory $old_sms_story):void {

		Gateway_Db_PivotPhone_PhoneChangeViaSmsStory::set(
			$change_phone_story_map,
			$old_sms_story->getSmsStoryData()->phone_number,
			[
				"status"     => Domain_User_Entity_ChangePhone_SmsStory::STATUS_DECLINED,
				"updated_at" => time(),
			]
		);
	}

	/**
	 * Создаем смс и генерируем код
	 *
	 * @return array
	 * @throws cs_IncorrectSaltVersion
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _doCreateSms(Domain_User_Entity_ChangePhone_Story $story, string $phone_number, string $change_phone_story_map):array {

		// генерируем код и текст сообщения, отправляем
		$sms_code      = generateConfirmCode();
		$locale_config = getConfig("LOCALE_TEXT");

		// формируем текст сообщения
		$sms_text = \BaseFrame\System\Locale::getText($locale_config, "sms_confirm", "change_phone", \BaseFrame\System\Locale::getLocale(), [
			"sms_code" => addConfirmCodeDash($sms_code),
		]);

		// генерируем sms_id
		$sms_id = generateUUID();

		// создаем запись об смс
		$sms_story = Domain_User_Entity_ChangePhone_SmsStory::createNewSmsStory(
			$change_phone_story_map,
			$phone_number,
			Domain_User_Entity_ChangePhone_Story::STAGE_SECOND,
			$sms_id,
			$sms_code,
		);

		// отправляем задачу в sms сервис
		Type_Sms_Queue::send($phone_number, $sms_text, Type_Sms_Analytics_Story::STORY_TYPE_PHONE_CHANGE, $change_phone_story_map, $sms_id);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// логируем в аналитику
		Type_Sms_Analytics_Story::onStartSecondStage($story->getStoryData()->user_id, $change_phone_story_map, $story->getExpiresAt(), $sms_id, $phone_number);

		return [$sms_code, $sms_story];
	}
}