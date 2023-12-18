<?php

namespace Compass\Pivot;

/**
 * Действие переотправки
 *
 * Class Domain_User_Action_Resend
 */
class Domain_User_Action_Resend {

	/**
	 * действие переотправки
	 *
	 * @param Struct_User_Auth_Story $story
	 *
	 * @return Struct_User_Auth_Story
	 * @throws cs_IncorrectSaltVersion
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 */
	public static function do(Struct_User_Auth_Story $story):Struct_User_Auth_Story {

		try {

			// получаем текущий проверочный код
			$sms_code = Domain_User_Entity_CachedConfirmCode::getAuthCode();
		} catch (cs_CacheIsEmpty) {

			// если в кэше не нашли проверочный код – логируем и генерируем новый
			Type_System_Admin::log("auth_story_resend", "не нашли проверочный код в кэше для {$story->auth_phone->auth_map}");
			$sms_code = generateConfirmCode();
		}

		$locale_config = getConfig("LOCALE_TEXT");

		// формируем текст сообщения
		$sms_text = \BaseFrame\System\Locale::getText($locale_config, "sms_confirm", "auth", \BaseFrame\System\Locale::getLocale(), [
			"sms_code" => addConfirmCodeDash($sms_code),
		]);

		// отправляем задачу в sms сервис
		$sms_id = Type_Sms_Queue::resend(
			$story->auth_phone->phone_number,
			$sms_text,
			$story->auth_phone->sms_id,
			Type_Sms_Analytics_Story::STORY_TYPE_AUTH,
			$story->auth_phone->auth_map
		);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// пишем в аналитику факт переотправки по запросу пользователя
		Type_Sms_Analytics_Story::onResend($story->auth->user_id, Type_Sms_Analytics_Story::STORY_TYPE_AUTH, $story->auth_phone->auth_map,
			$story->auth->expires_at, $sms_id, $story->auth_phone->phone_number);

		// добавляем записи в базу
		return Domain_User_Entity_StoryHandler_ResendStory::handle($story, $sms_code, $sms_id);
	}
}