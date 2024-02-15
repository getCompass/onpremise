<?php

namespace Compass\Pivot;

/**
 * класс, который описывает смс-логирование для различных flow (логин, регистрация, смена номера, 2fa и тд)
 */
class Type_Sms_Analytics_Story {

	/**
	 * все возможные типы действий
	 */
	public const STORY_TYPE_AUTH          = 1; // логин/регистрация
	public const STORY_TYPE_PHONE_CHANGE  = 2; // смена номера телефона
	public const STORY_TYPE_TWO_FA        = 3; // 2fa подтверждение действия (удаление аккаунта, удаление компании, самоувольнение ...)
	public const STORY_TYPE_OTHER_PRODUCT = 8; // флоу, начатый в другом продукте

	/**
	 * логируем в аналитику при старте flow
	 */
	public static function onStart(int $user_id, int $story_type, string $story_map, int $expires_at, string $sms_id, string $phone_number):void {

		$sms_analytic = Type_Sms_Analytics::getStruct(
			$sms_id,
			$phone_number,
			$expires_at,
			$story_type,
			$story_map,
			$user_id
		);
		Type_Sms_Analytics::onSentToRabbit($sms_analytic);
	}

	/**
	 * логируем аналитику при переотправке смс-кода в flow
	 */
	public static function onResend(int $user_id, int $story_type, string $story_map, int $expires_at, string $sms_id, string $phone_number):void {

		$sms_analytic = Type_Sms_Analytics::getStruct(
			$sms_id,
			$phone_number,
			$expires_at,
			$story_type,
			$story_map,
			$user_id
		);
		Type_Sms_Analytics::onUserResendSms($sms_analytic);
	}

	/**
	 * логируем аналитику при подтверждении смс-кода в flow
	 */
	public static function onConfirm(int $user_id, int $story_type, string $story_map, int $expires_at, string $sms_id, string $phone_number):void {

		$sms_analytic = Type_Sms_Analytics::getStruct(
			$sms_id,
			$phone_number,
			$expires_at,
			$story_type,
			$story_map,
			$user_id
		);
		Type_Sms_Analytics::onUserUseSms($sms_analytic);
	}

	/**
	 * логируем аналитику при старте второго этапа смены номера телефона
	 */
	public static function onStartSecondStage(int $user_id, string $story_map, int $expires_at, string $sms_id, string $phone_number):void {

		$sms_analytic = Type_Sms_Analytics::getStruct(
			$sms_id,
			$phone_number,
			$expires_at,
			self::STORY_TYPE_PHONE_CHANGE,
			$story_map,
			$user_id
		);
		Type_Sms_Analytics::onSendSmsForSecondStageChangePhone($sms_analytic);
	}

	/**
	 * логируем аналитику при протухании flow
	 */
	public static function onExpired(int $user_id, int $story_type, string $story_map, int $expires_at, string $sms_id, string $phone_number, string $phone_number_operator):void {

		$sms_analytic = Type_Sms_Analytics::getStruct(
			$sms_id,
			$phone_number,
			$expires_at,
			$story_type,
			$story_map,
			$user_id,
			phone_number_operator: $phone_number_operator
		);
		Type_Sms_Analytics::onStoryExpire($sms_analytic);
	}
}