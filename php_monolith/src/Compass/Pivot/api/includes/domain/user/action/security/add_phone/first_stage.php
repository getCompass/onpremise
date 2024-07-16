<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\LocaleTextNotFound;

/**
 * Первый этап добавления номера телефона
 */
class Domain_User_Action_Security_AddPhone_FirstStage {

	/**
	 * Выполняем начало процесса добавления номера телефона
	 *
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 * @throws LocaleTextNotFound
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function do(int $user_id, string $session_uniq, string $phone_number):array {

		$story      = Domain_User_Entity_Security_AddPhone_Story::createNewStory($user_id, $session_uniq);
		$story_data = $story->getStoryData();

		$story_id               = Gateway_Db_PivotPhone_PhoneAddStory::insert($story_data);
		$story                  = Domain_User_Entity_Security_AddPhone_Story::createFromAnotherStoryData($story_data, [
			"add_phone_story_id" => $story_id,
		]);
		$add_phone_story_map = $story->getStoryMap();

		$sms_story = self::_sendSmsCode($phone_number, $add_phone_story_map, $user_id, $story_data->expires_at);

		return [$story, $sms_story];
	}

	/**
	 * Отправляем смс
	 *
	 * @throws LocaleTextNotFound
	 * @throws \cs_UnpackHasFailed
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 */
	protected static function _sendSmsCode(string $phone_number, string $add_phone_story_map, int $user_id, int $expires_at):Domain_User_Entity_Security_AddPhone_SmsStory {

		// генерируем код
		$sms_code      = generateConfirmCode();
		$locale_config = getConfig("LOCALE_TEXT");

		// формируем текст сообщения
		$sms_text = \BaseFrame\System\Locale::getText($locale_config, "sms_confirm", "add_phone", \BaseFrame\System\Locale::getLocale(), [
			"sms_code" => addConfirmCodeDash($sms_code),
		]);

		// генерируем sms_id – случайный uuid
		$sms_id = generateUUID();

		// сохраняем запись об смс
		$sms_story = Domain_User_Entity_Security_AddPhone_SmsStory::createNewSmsStory(
			$add_phone_story_map,
			$phone_number,
			Domain_User_Entity_Security_AddPhone_Story::STAGE_FIRST,
			$sms_id,
			$sms_code,
		);
		Gateway_Db_PivotPhone_PhoneAddViaSmsStory::insert($sms_story->getSmsStoryData());

		// отправляем задачу в sms сервис
		$sms_id = Type_Sms_Queue::send($phone_number, $sms_text, Type_Sms_Analytics_Story::STORY_TYPE_PHONE_ADD, $add_phone_story_map, $sms_id);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// логируем в аналитику
		Type_Sms_Analytics_Story::onStart($user_id, Type_Sms_Analytics_Story::STORY_TYPE_PHONE_ADD, $add_phone_story_map, $expires_at, $sms_id, $phone_number);

		// сохраняем код смс в кэше
		Domain_User_Entity_CachedConfirmCode::storeAddPhoneCode($sms_code, Domain_User_Entity_Security_AddPhone_Story::STAGE_FIRST);

		return $sms_story;
	}
}