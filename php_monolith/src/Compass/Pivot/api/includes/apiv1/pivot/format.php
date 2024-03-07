<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для форматирование сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API
 * для форматирования стандартных сущностей
 *
 */
class Apiv1_Pivot_Format {

	// массив для преобразования внутреннего типа во внешний
	public const USER_TYPE_SCHEMA = [
		Type_User_Main::HUMAN       => "user",
		Type_User_Main::SYSTEM_BOT  => "system_bot",
		Type_User_Main::SUPPORT_BOT => "support_bot",
		Type_User_Main::OUTER_BOT   => "bot",
		Type_User_Main::USER_BOT    => "userbot",
		Type_User_Main::OPERATOR    => "operator",
	];

	/**
	 * Форматируем данные об аутентификации
	 *
	 * @param Struct_User_Auth_Info $auth_info
	 *
	 * @return array
	 */
	public static function authInfo(Struct_User_Auth_Info $auth_info):array {

		// собираем сущность для ответа
		$output = [
			"auth_map" => (string) $auth_info->auth_map,
			"type"     => (int) $auth_info->auth->type,
			"data"     => (object) self::_formatAuthInfoData($auth_info),
		];

		// если это аутентификация по номеру телефона, то поддерживаем старых клиентов
		// возвращаем структуру по старинке
		if (Domain_User_Entity_AuthStory::isPhoneNumberAuth($auth_info->auth->type)) {

			$output["next_resend"]        = (int) $auth_info->getAuthPhoneEntity()->getNextResendAt();
			$output["available_attempts"] = (int) $auth_info->getAuthPhoneEntity()->getAvailableAttempts(Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::SAAS_ERROR_COUNT_LIMIT);
			$output["expire_at"]          = (int) $auth_info->auth->expires_at;
			$output["phone_mask"]         = (string) (new \BaseFrame\System\PhoneNumber($auth_info->getAuthPhoneEntity()->getPhoneNumber()))->obfuscate();
		}

		return $output;
	}

	/**
	 * форматируем поле data для сущности auth_info
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 */
	protected static function _formatAuthInfoData(Struct_User_Auth_Info $auth_info):array {

		$output = [];

		if (Domain_User_Entity_AuthStory::isPhoneNumberAuth($auth_info->auth->type)) {

			$output = [
				"next_resend"        => (int) $auth_info->getAuthPhoneEntity()->getNextResendAt(),
				"available_attempts" => (int) $auth_info->getAuthPhoneEntity()->getAvailableAttempts(Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::SAAS_ERROR_COUNT_LIMIT),
				"expire_at"          => (int) $auth_info->auth->expires_at,
				"phone_mask"         => (string) (new \BaseFrame\System\PhoneNumber($auth_info->getAuthPhoneEntity()->getPhoneNumber()))->obfuscate(),
			];
		}

		if (Domain_User_Entity_AuthStory::isMailAuth($auth_info->auth->type)) {
			$output = [];
		}

		if (Domain_User_Entity_AuthStory::isMailResetPassword($auth_info->auth->type)) {
			$output = [];
		}

		return $output;
	}

	/**
	 * Форматируем данные о пользователе
	 *
	 * @param Struct_User_Info $user_info
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function user(Struct_User_Info $user_info):array {

		$output = [
			"user_id"      => (int) $user_info->user_id,
			"type"         => (string) self::_getUserOutputType($user_info->type),
			"is_verified"  => (int) $user_info->is_verified,
			"full_name"    => (string) $user_info->full_name,
			"data"         => (object) self::_getUserData(),
			"avatar_color" => (string) \BaseFrame\Domain\User\Avatar::getColorOutput($user_info->avatar_color_id),
		];

		// если есть мапа аватара - устанавливаем
		if ($user_info->avatar_file_map !== "") {

			$output["avatar"] = (object) [
				"file_map" => $user_info->avatar_file_map,
			];
		}

		return $output;
	}

	/**
	 * Возвращает тип пользователя для фронта на основе его npc_type
	 *
	 * @param string $user_type
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	protected static function _getUserOutputType(string $user_type):string {

		if (!isset(self::USER_TYPE_SCHEMA[$user_type])) {
			throw new ParseFatalException("there is no format output for npc type {$user_type}");
		}

		return self::USER_TYPE_SCHEMA[$user_type];
	}

	/**
	 * Форматируем данные об 2fa
	 *
	 * @param Struct_Db_PivotAuth_TwoFa $two_fa
	 *
	 * @return array
	 */
	public static function twoFaInfo(Struct_Db_PivotAuth_TwoFa $two_fa):array {

		return [
			"two_fa_map"  => (string) $two_fa->two_fa_map,
			"action_type" => (int) $two_fa->action_type,
			"expire_at"   => (int) $two_fa->expires_at,
		];
	}

	/**
	 * Форматируем данные об отправленной смс для 2fa
	 *
	 * @param Domain_User_Entity_TwoFa_Story $two_fa_story
	 *
	 * @return array
	 */
	public static function twoFaStoryInfo(Domain_User_Entity_TwoFa_Story $two_fa_story):array {

		return [
			"next_attempt"       => (int) $two_fa_story->getNextAttempt(),
			"phone_mask"         => (string) (new \BaseFrame\System\PhoneNumber($two_fa_story->getPhoneInfo()->phone_number))->obfuscate(),
			"available_attempts" => (int) $two_fa_story->getAvailableAttempts(),
			"expire_at"          => (int) $two_fa_story->getTwoFaInfo()->expires_at,
		];
	}

	/**
	 * Поле data в сущности пользователя
	 *
	 * @return int[]
	 */
	protected static function _getUserData():array {

		return [];
	}

	/**
	 * Форматируем константы звонков
	 *
	 * @param array $constants
	 *
	 * @return string[]
	 */
	public static function getCallConstants(array $constants):array {

		$output = [];
		foreach ($constants as $constant) {

			$output[] = [
				"name"  => (string) $constant["name"],
				"value" => (int) $constant["value"],
			];
		}
		return $output;
	}

	/**
	 * Форматируем активный звонок
	 *
	 * @param array $last_call_row
	 *
	 * @return array
	 */
	public static function getActiveCall(array $last_call_row):array {

		return [
			"call_key"   => (string) $last_call_row["call_key"],
			"company_id" => (int) $last_call_row["company_id"],
		];
	}
}
