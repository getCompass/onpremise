<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Класс для форматирования сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API
 * для форматирования стандартных сущностей
 *
 */
class Apiv2_Format {

	// массив для преобразования внутреннего типа stage во внешний для клиента
	protected const _STAGE_PHONE_SCHEMA = [
		Domain_User_Entity_Security_AddPhone_Story::STAGE_FIRST  => "entering_code",
		Domain_User_Entity_Security_AddPhone_Story::STAGE_SECOND => "finished",
	];

	// массив для преобразования внутреннего типа stage во внешний для клиента
	protected const _RESET_PASSWORD_MAIL_SCHEMA = [
		Domain_User_Entity_PasswordMail_Story::STAGE_START    => "entering_code",
		Domain_User_Entity_PasswordMail_Story::STAGE_FINISHED => "finished",
	];

	// массив для преобразования внутреннего типа stage во внешний для клиента
	protected const _CHANGE_MAIL_SCHEMA = [
		Domain_User_Entity_ChangeMail_Story::STAGE_FIRST    => "entering_first_code",
		Domain_User_Entity_ChangeMail_Story::STAGE_SECOND   => "entering_second_code",
		Domain_User_Entity_ChangeMail_Story::STAGE_FINISHED => "finished",
	];

	// массив для преобразования внутреннего типа scenario во внешний для клиента
	protected const _SCENARIO_MAIL_SCHEMA = [
		Domain_User_Scenario_Api_Security_Mail::SCENARIO_FULL_CHANGE  => "full_change_mail",
		Domain_User_Scenario_Api_Security_Mail::SCENARIO_SHORT_CHANGE => "short_change_mail",
	];

	/**
	 * Получить ответ со статусом компании
	 *
	 * @throws ParseFatalException
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["status_company" => "string"])]
	public static function companySystemStatus(int $company_system_status):array {

		if (!isset(Domain_Company_Entity_Company::SYSTEM_COMPANY_STATUS_SCHEMA[$company_system_status])) {
			throw new ParseFatalException("unknown company system type");
		}
		return [
			"status_company" => (string) Domain_Company_Entity_Company::SYSTEM_COMPANY_STATUS_SCHEMA[$company_system_status],
		];
	}

	/**
	 * формируем ответ для служебных данных клиента
	 */
	public static function formatStartData(array $start_data):array {

		$output = [];

		// отдаем конфиг эмоджи если есть
		$output["emoji_keywords_list"] = isset($start_data["emoji_keywords_list"]) ? (array) $start_data["emoji_keywords_list"] : [];

		// отдаем видео-онбординг для чата наймы и увольнения
		$output["onboarding_videos_list"] = isset($start_data["onboarding_videos_list"])
			? (array) $start_data["onboarding_videos_list"] : [];

		// отдаем конфиг приложения
		$output["app_config_list"] = isset($start_data["app_config_list"])
			? (array) $start_data["app_config_list"] : [];

		// отдаем список фич
		$output["feature_list"] = isset($start_data["feature_list"])
			? (array) $start_data["feature_list"] : [];

		// список доступных способов аутентификации
		$output["available_auth_method_list"] = $start_data["available_auth_method_list"];

		// список доступных способов аутентификации гостей
		$output["available_auth_guest_method_list"] = $start_data["available_auth_guest_method_list"];

		// тип сервера
		$output["server_type"] = $start_data["server_type"];

		return $output;
	}

	/**
	 * Форматируем тарифный план числа участников.
	 * Здесь есть немного логики, для привода наших значений к клиентским.
	 *
	 * @param \Tariff\Plan\MemberCount\MemberCount $member_count_plan
	 * @param int                                  $member_count
	 *
	 * @return array
	 */
	#[ArrayShape(["active_till" => "int", "limit" => "int", "current" => "int", "extend_policy" => "string", "allowed_action_list" => "array"])]
	public static function memberCountPlan(\Tariff\Plan\MemberCount\MemberCount $member_count_plan, int $member_count):array {

		// эту логику нужно держать в синхронизации с методом tariff/get пространства
		// --------------------------------------------------------------------------
		if (($member_count_plan->isActive(time()) && !$member_count_plan->isFree(time())) || ($member_count_plan->isTrial(time()) && $member_count_plan->getLimit() > 10)) {
			$allowed_action_list[] = "prolong";
		} elseif ((!$member_count_plan->isActive(time()) || $member_count_plan->isFree(time())) && !$member_count_plan->isTrial(time())) {
			$allowed_action_list[] = "activate";
		}

		if ($member_count_plan->isTrial(time()) || !$member_count_plan->isFree(time())) {
			$allowed_action_list[] = "change";
		}
		// --------------------------------------------------------------------------

		$active_till   = $member_count_plan->getActiveTill();
		$extend_policy = $member_count_plan->getExtendPolicyRule();

		if ($extend_policy === \Tariff\Plan\MemberCount\OptionExtendPolicy::TRIAL && $active_till < time()) {
			$extend_policy = \Tariff\Plan\MemberCount\OptionExtendPolicy::NEVER;
		}

		// а если бесплатный, но не триальный, то отдаем бесконечную длительность
		if ($member_count_plan->isFree(time()) && !$member_count_plan->isTrial(time())) {
			$active_till = 0;
		}

		return [
			"active_till"         => $active_till,
			"limit"               => $member_count_plan->getLimit(),
			"current"             => $member_count,
			"extend_policy"       => $extend_policy,
			"allowed_action_list" => $allowed_action_list,
		];
	}

	/**
	 * Форматируем ответ для добавления номера телефона
	 */
	public static function addPhone(
		Domain_User_Entity_Security_AddPhone_Story    $story,
		Domain_User_Entity_Security_AddPhone_SmsStory $sms_story
	):array {

		return [
			"add_phone_story_info" => (object) [
				"add_phone_story_map" => (string) $story->getStoryMap(),
				"data"                => (object) [
					"next_resend"             => (int) $sms_story->getNextResend(),
					"code_available_attempts" => (int) $sms_story->getAvailableAttempts(),
					"expire_at"               => (int) $story->getExpiresAt(),
					"stage"                   => (string) self::_STAGE_PHONE_SCHEMA[$sms_story->getSmsStoryData()->stage],
				],
			],
		];
	}

	/**
	 * Форматируем ответ для пересылки смс
	 */
	public static function resendSms(
		Domain_User_Entity_Security_AddPhone_Story|Domain_User_Entity_ChangePhone_Story       $story,
		Domain_User_Entity_Security_AddPhone_SmsStory|Domain_User_Entity_ChangePhone_SmsStory $sms_story,
		string                                                                                $phone_story_type
	):array {

		$phone_story_key = match ($phone_story_type) {
			Domain_User_Entity_Security_AddPhone_Story::ACTION_TYPE => Type_Pack_AddPhoneStory::doEncrypt($story->getStoryMap()),
			Domain_User_Entity_ChangePhone_Story::ACTION_TYPE => Type_Pack_ChangePhoneStory::doEncrypt($story->getStoryMap()),
			default => "",
		};

		return [
			"phone_story_info" => (object) [
				"phone_story_key"  => (string) $phone_story_key,
				"phone_story_type" => (string) $phone_story_type,
				"data"             => (object) [
					"next_resend"             => (int) $sms_story->getNextResend(),
					"code_available_attempts" => (int) $sms_story->getAvailableAttempts(),
					"expire_at"               => (int) $story->getExpiresAt(),
					"stage"                   => (string) self::_STAGE_PHONE_SCHEMA[$sms_story->getSmsStoryData()->stage],
				],
			],
		];
	}

	/**
	 * Форматируем ответ для добавления номера телефона
	 */
	public static function mailResetPassword(
		Domain_User_Entity_PasswordMail_Story     $story,
		Domain_User_Entity_PasswordMail_CodeStory $code_story
	):array {

		return [
			"password_mail_story_info" => (object) [
				"password_mail_story_map" => (string) $story->getStoryMap(),
				"type"                    => $story->getStoryData()->type,
				"data"                    => (object) [
					"next_resend"             => (int) $code_story->getNextResend(),
					"code_available_attempts" => (int) $code_story->getAvailableAttempts(),
					"expire_at"               => (int) $story->getExpiresAt(),
					"stage"                   => (string) self::_RESET_PASSWORD_MAIL_SCHEMA[$story->getStage()],
				],
			],
		];
	}

	/**
	 * Форматируем ответ для смены почты
	 */
	public static function changeMail(
		Domain_User_Entity_ChangeMail_Story     $story,
		Domain_User_Entity_ChangeMail_CodeStory $code_story,
		string                                  $scenario):array {

		return [
			"change_mail_story_info" => (object) [
				"change_mail_story_map" => (string) $story->getStoryMap(),
				"scenario"              => (string) self::_SCENARIO_MAIL_SCHEMA[$scenario],
				"data"                  => (object) [
					"next_resend"             => (int) $code_story->getNextResend(),
					"code_available_attempts" => (int) $code_story->getAvailableAttempts(),
					"expire_at"               => (int) $story->getExpiresAt(),
					"stage"                   => (string) Domain_User_Entity_Auth_Config::isMailAuthorization2FAEnabled()
						? self::_CHANGE_MAIL_SCHEMA[$story->getStage()] : self::_CHANGE_MAIL_SCHEMA[Domain_User_Entity_ChangeMail_Story::STAGE_FINISHED],
				],
			],
		];
	}

	/**
	 * Форматируем ответ для переотправки проверочного кода на почту
	 */
	public static function resendCode(
		Domain_User_Entity_Security_AddMail_Story|Domain_User_Entity_PasswordMail_Story|Domain_User_Entity_ChangeMail_Story             $story,
		Domain_User_Entity_Security_AddMail_CodeStory|Domain_User_Entity_PasswordMail_CodeStory|Domain_User_Entity_ChangeMail_CodeStory $story_code,
		string                                                                                                                          $mail_story_type,
		string                                                                                                                          $stage
	):array {

		$mail_story_key = match ($mail_story_type) {
			Domain_User_Entity_Security_AddMail_Story::ACTION_TYPE => Type_Pack_AddMailStory::doEncrypt($story->getStoryMap()),
			Domain_User_Entity_PasswordMail_Story::ACTION_TYPE_RESET_PASSWORD => Type_Pack_PasswordMailStory::doEncrypt($story->getStoryMap()),
			Domain_User_Entity_ChangeMail_Story::ACTION_TYPE => Type_Pack_ChangeMailStory::doEncrypt($story->getStoryMap()),
			default => throw new ParseFatalException("unknown type from format resend code"),
		};

		return [
			"mail_story_info" => (object) [
				"mail_story_key"  => (string) $mail_story_key,
				"mail_story_type" => (string) $mail_story_type,
				"data"            => (object) [
					"next_resend"             => (int) $story_code->getNextResend(),
					"code_available_attempts" => (int) $story_code->getAvailableAttempts(),
					"expire_at"               => (int) $story->getExpiresAt(),
					"stage"                   => (string) $stage,
				],
			],
		];
	}

	/**
	 * Форматируем ответ для добавления почты пользователю
	 */
	public static function addMail(
		Domain_User_Entity_Security_AddMail_Story     $story,
		Domain_User_Entity_Security_AddMail_CodeStory $code_story,
		string                                        $scenario,
		string                                        $stage
	):array {

		return [
			"add_mail_story_info" => (object) [
				"add_mail_story_map" => (string) $story->getStoryMap(),
				"scenario"           => (string) $scenario,
				"data"               => (object) [
					"next_resend"             => (int) Domain_User_Entity_Auth_Config::isMailAuthorization2FAEnabled() ? $code_story->getNextResend() : 0,
					"code_available_attempts" => (int) Domain_User_Entity_Auth_Config::isMailAuthorization2FAEnabled() ? $code_story->getAvailableAttempts() : 0,
					"expire_at"               => (int) $story->getExpiresAt(),
					"stage"                   => (string) $stage,
				],
			],
		];
	}

	/**
	 * Форматируем данные сессии для устройства пользователя
	 */
	public static function sessionDevice(string $public_session_id, Struct_Db_PivotUser_SessionActive $session_active, bool $is_current):array {

		return [
			"session_id"     => (string) $public_session_id,
			"is_current"     => (int) $is_current ? 1 : 0,
			"login_at"       => (int) $session_active->login_at,
			"last_online_at" => (int) $session_active->last_online_at,
			"device_name"    => (string) Domain_User_Entity_SessionExtra::getDeviceName($session_active->extra),
			"device_type"    => (string) Domain_User_Entity_SessionExtra::getOutputDeviceType($session_active->extra),
			"login_type"     => (int) Domain_User_Entity_SessionExtra::getLoginType($session_active->extra),
			"app_version"    => (string) Domain_User_Entity_SessionExtra::getAppVersion($session_active->extra),
			"server_version" => (string) Domain_User_Entity_SessionExtra::getServerVersion($session_active->extra),
		];
	}

	/**
	 * Форматируем ответ для получения онлайна пользователя
	 */
	public static function getOnline(int $last_online_at):array {

		return [
			"last_online_at" => (int) $last_online_at,
		];
	}

	/**
	 * Форматируем ответ для получения списка онлайна пользователей
	 */
	public static function getOnlineList(array $user_online_list):array {

		// подводим под формат
		$formatted_user_online_list = [];
		foreach ($user_online_list as $v) {
			$formatted_user_online_list[] = self::_makeOutputOnlineList($v);
		}

		return [
			"online_list" => (array) $formatted_user_online_list,
		];
	}

	/**
	 * Формируем массив online_list
	 *
	 * @param Struct_Db_PivotUser_UserActivityList $user_online
	 *
	 * @return array
	 */
	protected static function _makeOutputOnlineList(Struct_Db_PivotUser_UserActivityList $user_online):array {

		return [
			"user_id"        => (int) $user_online->user_id,
			"last_online_at" => (int) $user_online->last_ws_ping_at,
		];
	}
}