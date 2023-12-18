<?php

namespace Compass\Pivot;

/**
 * Первый этап смены номера телефона
 */
class Domain_User_Action_ChangePhone_FirstStage {

	/**
	 * Выполняем начало процесса смены номера телефона
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_UserPhoneSecurityNotFound
	 */
	public static function do(int $user_id, string $session_uniq):array {

		$phone_number = Domain_User_Entity_Phone::getPhoneByUserId($user_id);

		$story      = Domain_User_Entity_ChangePhone_Story::createNewStory($user_id, $session_uniq);
		$story_data = $story->getStoryData();

		$story_id               = Gateway_Db_PivotPhone_PhoneChangeStory::insert($story_data);
		$story                  = Domain_User_Entity_ChangePhone_Story::createFromAnotherStoryData($story_data, [
			"change_phone_story_id" => $story_id,
		]);
		$change_phone_story_map = $story->getStoryMap();

		$sms_story = self::_sendSmsCode($phone_number, $change_phone_story_map, $user_id, $story_data->expires_at);

		return [$story, $sms_story];
	}

	/**
	 * Отправляем смс на старый номер телефона
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _sendSmsCode(string $phone_number, string $change_phone_story_map, int $user_id, int $expires_at):Domain_User_Entity_ChangePhone_SmsStory {

		// генерируем код
		$sms_code      = generateConfirmCode();
		$locale_config = getConfig("LOCALE_TEXT");

		// формируем текст сообщения
		$sms_text = \BaseFrame\System\Locale::getText($locale_config, "sms_confirm", "change_phone", \BaseFrame\System\Locale::getLocale(), [
			"sms_code" => addConfirmCodeDash($sms_code),
		]);

		// генерируем sms_id – случайный uuid
		$sms_id = generateUUID();

		// сохраняем запись об смс
		$sms_story = Domain_User_Entity_ChangePhone_SmsStory::createNewSmsStory(
			$change_phone_story_map,
			$phone_number,
			Domain_User_Entity_ChangePhone_Story::STAGE_FIRST,
			$sms_id,
			$sms_code,
		);
		Gateway_Db_PivotPhone_PhoneChangeViaSmsStory::insert($sms_story->getSmsStoryData());

		// отправляем задачу в sms сервис
		$sms_id = Type_Sms_Queue::send($phone_number, $sms_text, Type_Sms_Analytics_Story::STORY_TYPE_PHONE_CHANGE, $change_phone_story_map, $sms_id);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// логируем в аналитику
		Type_Sms_Analytics_Story::onStart($user_id, Type_Sms_Analytics_Story::STORY_TYPE_PHONE_CHANGE, $change_phone_story_map, $expires_at, $sms_id, $phone_number);

		// сохраняем код смс в кэше
		Domain_User_Entity_CachedConfirmCode::storeChangePhoneCode($sms_code, Domain_User_Entity_ChangePhone_Story::STAGE_FIRST);

		return $sms_story;
	}
}