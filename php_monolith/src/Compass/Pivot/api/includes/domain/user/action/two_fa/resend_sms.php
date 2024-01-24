<?php

namespace Compass\Pivot;

/**
 * Действие переотправки смс для подтверждения 2fa действия
 *
 * Class Domain_User_Action_TwoFa_ResendSms
 */
class Domain_User_Action_TwoFa_ResendSms {

	/**
	 * действие переотправки смс
	 *
	 * @param Domain_User_Entity_TwoFa_Story $story
	 *
	 * @return Domain_User_Entity_TwoFa_Story
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 */
	public static function do(Domain_User_Entity_TwoFa_Story $story):Domain_User_Entity_TwoFa_Story {

		try {

			// пробуем достать прошлое смс из кеша
			$sms_code = Domain_User_Entity_CachedConfirmCode::getAuthCode();
		} catch (cs_CacheIsEmpty) {
			$sms_code = generateConfirmCode();
		}

		// получим ключ с которой будем брать текст по типу токена
		$key = Domain_User_Entity_TwoFa_TwoFa::getGroupNameByActionType($story->getTwoFaInfo()->action_type);

		// формируем текст сообщения
		$sms_text = \BaseFrame\System\Locale::getText(getConfig("LOCALE_TEXT"), "sms_confirm", $key, \BaseFrame\System\Locale::getLocale(), [
			"sms_code" => addConfirmCodeDash($sms_code),
		]);

		// отправляем задачу в sms сервис
		$sms_id       = self::_resendSms($story, $sms_text);
		$time         = time();
		$next_attempt = $time + Domain_User_Entity_TwoFa_Story::NEXT_ATTEMPT_AFTER;

		$phone_info = $story->getPhoneInfo();
		Gateway_Db_PivotAuth_TwoFaPhoneList::set($phone_info->two_fa_map, [
			"resend_count"   => "resend_count + 1",
			"updated_at"     => $time,
			"next_resend_at" => $next_attempt,
			"sms_code_hash"  => Type_Hash_Code::makeHash($sms_code),
			"sms_id"         => $sms_id,
		]);

		$phone_info->next_resend_at = $next_attempt;
		$phone_info->resend_count++;

		return new Domain_User_Entity_TwoFa_Story($story->getTwoFaInfo(), $phone_info);
	}

	/**
	 * совершаем переотправку
	 *
	 * @return string
	 * @throws \queryException
	 */
	protected static function _resendSms(Domain_User_Entity_TwoFa_Story $story, string $sms_text):string {

		// получаем информацию о номере, куда ранее отправили смс
		$phone_info = $story->getPhoneInfo();

		// получаем информацию о two_fa попытке
		$two_fa_info = $story->getTwoFaInfo();

		// совершаем переотправку
		$sms_id = Type_Sms_Queue::resend(
			$phone_info->phone_number,
			$sms_text,
			$phone_info->sms_id,
			Type_Sms_Analytics_Story::STORY_TYPE_TWO_FA,
			$two_fa_info->two_fa_map
		);

		// увеличиваем счетчик отправленных смс
		Gateway_Bus_CollectorAgent::init()->inc("row54");

		// пишем в аналитику факт переотправки по запросу пользователя
		Type_Sms_Analytics_Story::onResend($two_fa_info->user_id, Type_Sms_Analytics_Story::STORY_TYPE_TWO_FA, $two_fa_info->two_fa_map,
			$two_fa_info->expires_at, $sms_id, $phone_info->phone_number);

		return $sms_id;
	}
}
