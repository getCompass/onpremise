<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Константные значения пользователя
 */
class Type_User_Main {

	public const NPC_TYPE_HUMAN              = 1;   // пользователь
	public const NPC_TYPE_BOT                = 2;   // бот
	public const NPC_TYPE_SYSTEM_BOT_NOTICE  = 5;   // тип системный бот, подтип Оповещение
	public const NPC_TYPE_SYSTEM_BOT_REMIND  = 6;   // тип системный бот, подтип Напоминание
	public const NPC_TYPE_SYSTEM_BOT_SUPPORT = 7;   // тип системный бот, подтип Служба поддержки
	public const NPC_TYPE_OUTER_BOT          = 101; // внешний бот
	public const NPC_TYPE_USER_BOT           = 128; // пользовательский бот
	public const NPC_TYPE_OPERATOR           = 171; // оператор поддержки

	public const HUMAN       = "human";
	public const SYSTEM_BOT  = "systembot";
	public const SUPPORT_BOT = "supportbot";
	public const OUTER_BOT   = "outerbot";
	public const USER_BOT    = "userbot";
	public const OPERATOR    = "operator";

	public const DEFAULT_SHORT_DESCRIPTION = "Пользователь Compass";

	protected const _SYSTEM_BOT_NPC_TYPE_FROM  = 5;
	protected const _SYSTEM_BOT_NPC_TYPE_TO    = 50;
	protected const _SUPPORT_BOT_NPC_TYPE_FROM = 51;
	protected const _SUPPORT_BOT_NPC_TYPE_TO   = 100;
	protected const _OUTER_BOT_NPC_TYPE_FROM   = 101;
	protected const _OUTER_BOT_NPC_TYPE_TO     = 127;

	protected const _USER_BOT_NPC_TYPE_FROM = 128;
	protected const _USER_BOT_NPC_TYPE_TO   = 170;

	// список подтипов для системного бота
	protected const _SYSTEM_BOT_SUBTYPE = [
		self::NPC_TYPE_SYSTEM_BOT_NOTICE  => "notice_bot",
		self::NPC_TYPE_SYSTEM_BOT_REMIND  => "remind_bot",
		self::NPC_TYPE_SYSTEM_BOT_SUPPORT => "support_bot",
	];

	// -------------------------------------------------------
	// EXTRA VARIABLES
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 8; // текущая версия extra
	protected const _EXTRA_SCHEME  = [  // массив с версиями extra

		1 => [
			"is_disabled" => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at" => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"mbti_type"   => "", // тип личности
			"badge"       => [
				"content"  => "", // текст баджа
				"color_id" => 0,  // цвет баджа
			],
		],

		2 => [
			"is_disabled"         => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"         => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"mbti_type"           => "", // тип личности
			"badge"               => [
				"content"  => "", // текст баджа
				"color_id" => 0,  // цвет баджа
			],
			"discount_percent"    => 0, // процент скидки при оплате плана
			"partner_fee_percent" => 0, // процент доли, которую получает партнер от оплат собственника за план
		],

		3 => [
			"is_disabled"         => 0, // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"         => 0, // время, показывающее когда деактировали аккаунт сотрудника
			"discount_percent"    => 0, // процент скидки при оплате плана
			"partner_fee_percent" => 0, // процент доли, которую получает партнер от оплат собственника за план
		],

		4 => [
			"is_disabled"                              => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"                              => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"discount_percent"                         => 0,  // процент скидки при оплате плана
			"partner_fee_percent"                      => 0,  // процент доли, которую получает партнер от оплат собственника за план
			"user_disabled_analytics_event_group_list" => [], // список логов, отключенные для пользователя
		],

		5 => [
			"is_disabled"                              => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"                              => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"discount_percent"                         => 0,  // процент скидки при оплате плана
			"partner_fee_percent"                      => 0,  // процент доли, которую получает партнер от оплат собственника за план
			"user_disabled_analytics_event_group_list" => [], // список логов, отключенные для пользователя
			"avg_screen_time"                          => 0, // среднее экранное время пользователя
			"total_action_count"                       => 0, // общее количество действий пользователя
		],

		6 => [
			"is_disabled"                              => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"                              => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"discount_percent"                         => 0,  // процент скидки при оплате плана
			"partner_fee_percent"                      => 0,  // процент доли, которую получает партнер от оплат собственника за план
			"user_disabled_analytics_event_group_list" => [], // список логов, отключенные для пользователя
			"avg_screen_time"                          => 0, // среднее экранное время пользователя
			"total_action_count"                       => 0, // общее количество действий пользователя
			"avg_message_answer_time"                  => 0, // среднее время ответа на сообщения
		],

		7 => [
			"is_disabled"                              => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"                              => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"discount_percent"                         => 0,  // процент скидки при оплате плана
			"partner_fee_percent"                      => 0,  // процент доли, которую получает партнер от оплат собственника за план
			"user_disabled_analytics_event_group_list" => [], // список логов, отключенные для пользователя
			"avg_screen_time"                          => 0, // среднее экранное время пользователя
			"total_action_count"                       => 0, // общее количество действий пользователя
			"avg_message_answer_time"                  => 0, // среднее время ответа на сообщения
			"avatar_color_id"                          => 0, // id аватара пользователя
		],

		8 => [
			"is_disabled"                              => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"                              => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"discount_percent"                         => 0,  // процент скидки при оплате плана
			"partner_fee_percent"                      => 0,  // процент доли, которую получает партнер от оплат собственника за план
			"user_disabled_analytics_event_group_list" => [], // список логов, отключенные для пользователя
			"avg_screen_time"                          => 0, // среднее экранное время пользователя
			"total_action_count"                       => 0, // общее количество действий пользователя
			"avg_message_answer_time"                  => 0, // среднее время ответа на сообщения
			"avatar_color_id"                          => 0, // id аватара пользователя
			"onboarding_list"                          => [], // список онбордингов
		],
	];

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Получить информацию о переданном пользователе
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_PivotUser_User
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function get(int $user_id):Struct_Db_PivotUser_User {

		// получаем информацию от go_pivot_cache
		return Gateway_Bus_PivotCache::getUserInfo($user_id);
	}

	// -------------------------------------------------------
	// PUBLIC METHODS Определение типа пользователя
	// -------------------------------------------------------

	/**
	 * Возвращает тип пользователя
	 *
	 * @param int $npc_type
	 *
	 * @return string
	 * @throws \parseException
	 */
	public static function getUserType(int $npc_type):string {

		if (self::isHuman($npc_type)) {

			// человек
			return self::HUMAN;
		}

		if (self::isSystemBot($npc_type)) {

			// системный бот
			return self::SYSTEM_BOT;
		}

		if (self::isSupportBot($npc_type)) {

			// системный бот поддержки
			return self::SUPPORT_BOT;
		}

		if (self::isOuterBot($npc_type)) {

			// внешний бот
			return self::OUTER_BOT;
		}

		if (self::isUserBot($npc_type)) {

			// пользовательский бот
			return self::USER_BOT;
		}

		if (self::isOperator($npc_type)) {

			// оператор поддержки
			return self::OPERATOR;
		}

		throw new ParseFatalException("can't get user type — unexpected npc type $npc_type");
	}

	/**
	 * Решаем, пустой ли профиль у пользователя
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function isEmptyProfile(int $user_id):bool {

		try {
			$user_info = self::get($user_id);
		} catch (cs_UserNotFound) {
			throw new EndpointAccessDeniedException("not found user");
		}
		return Domain_User_Entity_User::isEmptyProfile($user_info);
	}

	/**
	 * Решаем, является ли пользователь человеком
	 *
	 * @param int $npc_type
	 *
	 * @return bool
	 */
	public static function isHuman(int $npc_type):bool {

		return $npc_type == self::NPC_TYPE_HUMAN;
	}

	/**
	 * Решаем, является ли пользователь оператором
	 *
	 * @param int $npc_type
	 *
	 * @return bool
	 */
	public static function isOperator(int $npc_type):bool {

		return $npc_type == self::NPC_TYPE_OPERATOR;
	}

	/**
	 * Решаем, является ли пользователь системным ботом
	 *
	 * @param int $npc_type
	 *
	 * @return bool
	 */
	public static function isSystemBot(int $npc_type):bool {

		return $npc_type >= self::_SYSTEM_BOT_NPC_TYPE_FROM && $npc_type <= self::_SYSTEM_BOT_NPC_TYPE_TO;
	}

	/**
	 * Решаем, является ли пользователь ботом поддержки
	 *
	 * @param int $npc_type
	 *
	 * @return bool
	 */
	public static function isSupportBot(int $npc_type):bool {

		return $npc_type >= self::_SUPPORT_BOT_NPC_TYPE_FROM && $npc_type <= self::_SUPPORT_BOT_NPC_TYPE_TO;
	}

	/**
	 * Решаем, является ли пользователь внешним ботом
	 *
	 * @param int $npc_type
	 *
	 * @return bool
	 */
	public static function isOuterBot(int $npc_type):bool {

		return ($npc_type >= self::_OUTER_BOT_NPC_TYPE_FROM && $npc_type <= self::_OUTER_BOT_NPC_TYPE_TO)
			|| ($npc_type > 0 && $npc_type < self::_SYSTEM_BOT_NPC_TYPE_FROM);
	}

	/**
	 * Решаем, является ли пользовательским ботом
	 *
	 * @param int $npc_type
	 *
	 * @return bool
	 */
	public static function isUserBot(int $npc_type):bool {

		return $npc_type >= self::_USER_BOT_NPC_TYPE_FROM && $npc_type <= self::_USER_BOT_NPC_TYPE_TO;
	}

	/**
	 * Проверяем, является ли пользователь ботом
	 *
	 * @param int $npc_type
	 *
	 * @return bool
	 */
	public static function isBot(int $npc_type):bool {

		return self::isSystemBot($npc_type) || self::isSupportBot($npc_type) || self::isOuterBot($npc_type);
	}

	/**
	 * получаем подтип системного бота
	 */
	public static function getSystemBotSubtype(int $npc_type):string {

		// если это не системный бот возвращаем пустую строку
		// не ругаемся, так как используется в батчинге
		if (!self::isSystemBot($npc_type)) {
			return "";
		}

		// если это неизвестный тип системного бота
		if (!isset(self::_SYSTEM_BOT_SUBTYPE[$npc_type])) {
			return "";
		}

		// возвращаем подтип системного бота
		return self::_SYSTEM_BOT_SUBTYPE[$npc_type];
	}

	// -------------------------------------------------------
	// PUBLIC METHODS OTHER
	// -------------------------------------------------------

	/**
	 * Получаем незаблокированных в системе пользователей
	 *
	 * @param array $user_info_list
	 *
	 * @return array
	 */
	public static function getNotDisabledUsers(array $user_info_list):array {

		$not_disabled_user_list = [];
		foreach ($user_info_list as $v) {

			// если пользователь заблокирован, то пропускаем
			if (self::isDisabledProfile($v["extra"])) {
				continue;
			}

			$not_disabled_user_list[] = $v;
		}
		return $not_disabled_user_list;
	}

	/**
	 * Получаем список id заблокированных в системе пользователей
	 *
	 * @param array $user_info_list
	 *
	 * @return array
	 */
	public static function getDisabledUserIdList(array $user_info_list):array {

		$disabled_user_id_list = [];
		foreach ($user_info_list as $v) {

			if (self::isDisabledProfile($v["extra"])) {
				$disabled_user_id_list[] = $v["user_id"];
			}
		}

		return $disabled_user_id_list;
	}

	// -------------------------------------------------------
	// EXTRA METHODS
	// -------------------------------------------------------

	/**
	 * Возвращает текущую структуру extra с default значениями
	 *
	 * @return array
	 */
	public static function initExtra():array {

		return [
			"handler_version" => self::_EXTRA_VERSION,
			"extra"           => self::_EXTRA_SCHEME[self::_EXTRA_VERSION],
		];
	}

	/**
	 * Помечаем профиль деактивированным
	 *
	 * @param array    $extra
	 * @param int|null $disabled_at
	 *
	 * @return array
	 */
	public static function setProfileDisabled(array $extra, int $disabled_at = null):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		$extra["extra"]["is_disabled"] = 1;

		// записываем время деактивации
		$extra["extra"]["disabled_at"] = is_null($disabled_at) ? time() : $disabled_at;
		return $extra;
	}

	/**
	 * Получаем время деактивации аккаунта
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getProfileDisabledAt(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["disabled_at"];
	}

	/**
	 * Помечаем профиль разблокированным
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	public static function setProfileEnabled(array $extra):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		$extra["extra"]["is_disabled"] = 0;

		// обнуляем время деактивации
		$extra["extra"]["disabled_at"] = 0;
		return $extra;
	}

	/**
	 * Проверяем, активен ли аккаунт пользователя
	 *
	 * @param array $extra
	 *
	 * @return bool
	 */
	public static function isDisabledProfile(array $extra):bool {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_disabled"] == 1;
	}

	/**
	 * Установить процент скидки пользователю на оплату плана
	 *
	 * @param array $extra
	 * @param int   $discount_percent
	 *
	 * @return array
	 */
	public static function setDiscountPercent(array $extra, int $discount_percent):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем discount_percent
		$extra["extra"]["discount_percent"] = $discount_percent;

		return $extra;
	}

	/**
	 * Получить процент скидки пользователя на оплату плана
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getDiscountPercent(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["discount_percent"];
	}

	/**
	 * Установить процент доли, которую получает партнер от оплат собственника за план
	 *
	 * @param array $extra
	 * @param int   $partner_fee_percent
	 *
	 * @return array
	 */
	public static function setPartnerFeePercent(array $extra, int $partner_fee_percent):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем partner_fee_percent
		$extra["extra"]["partner_fee_percent"] = $partner_fee_percent;

		return $extra;
	}

	/**
	 * Получить процент доли, которую получает партнер от оплат собственника за план
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getPartnerFeePercent(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["partner_fee_percent"];
	}

	/**
	 * Получить отключенные для пользователя группы логирования событий
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	public static function getDisabledAnalyticsEventGroupList(array $extra):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["user_disabled_analytics_event_group_list"];
	}

	/**
	 * Получить среднее экранное время пользователя
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getAvgScreenTime(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["avg_screen_time"];
	}

	/**
	 * Сохранить среднее экранное время пользователя
	 *
	 * @param array $extra
	 * @param int   $avg_screen_time
	 *
	 * @return array
	 */
	public static function setAvgScreenTime(array $extra, int $avg_screen_time):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем avg_screen_time
		$extra["extra"]["avg_screen_time"] = $avg_screen_time;

		return $extra;
	}

	/**
	 * Получить общее количество действий пользователя
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getTotalActionCount(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["total_action_count"];
	}

	/**
	 * Сохранить общее количество действий пользователя
	 *
	 * @param array $extra
	 * @param int   $total_action_count
	 *
	 * @return array
	 */
	public static function setTotalActionCount(array $extra, int $total_action_count):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем total_action_count
		$extra["extra"]["total_action_count"] = $total_action_count;

		return $extra;
	}

	/**
	 * Получить среднее время ответа пользователя на сообщения
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getAvgMessageAnswerTime(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["avg_message_answer_time"];
	}

	/**
	 * Сохранить среднее время ответа пользователя на сообщения
	 *
	 * @param array $extra
	 * @param int   $avg_message_answer_time
	 *
	 * @return array
	 */
	public static function setAvgMessageAnswerTime(array $extra, int $avg_message_answer_time):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем avg_message_answer_time
		$extra["extra"]["avg_message_answer_time"] = $avg_message_answer_time;

		return $extra;
	}

	/**
	 * Получить id цвета аватара
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getAvatarColorId(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["avatar_color_id"];
	}

	/**
	 * Установить id цвета аватара
	 *
	 * @param array $extra
	 * @param int   $avatar_color_id
	 *
	 * @return array
	 */
	public static function setAvatarColorId(array $extra, int $avatar_color_id):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем avg_message_answer_time
		$extra["extra"]["avatar_color_id"] = $avatar_color_id;

		return $extra;
	}

	/**
	 * Получить список онбордингов
	 *
	 * @param array $extra
	 *
	 * @return Struct_User_Onboarding[]
	 */
	public static function getOnboardingList(array $extra):array {

		$onboarding_list = [];

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// форматируем массив онбординга в объект
		foreach ($extra["extra"]["onboarding_list"] as $type => $onboarding_arr) {
			$onboarding_list[$type] = Struct_User_Onboarding::fromArray($onboarding_arr);
		}

		return $onboarding_list;
	}

	/**
	 * Установить список онбордингов
	 *
	 * @param array                    $extra
	 * @param Struct_User_Onboarding[] $onboarding_list
	 *
	 * @return array
	 */
	public static function setOnboardingList(array $extra, array $onboarding_list):array {

		$onboardings_arr = [];

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// форматируем объекты онбординга в массив
		foreach ($onboarding_list as $onboarding) {
			$onboardings_arr[] = $onboarding->toArray();
		}

		// обновляем список онбордингов
		$extra["extra"]["onboarding_list"] = $onboardings_arr;

		return $extra;
	}
	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Актуализирует структуру extra
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	protected static function _getExtra(array $extra):array {

		// если extra не проинициализированна
		if (!isset($extra["handler_version"])) {

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]           = array_merge(self::_EXTRA_SCHEME[self::_EXTRA_VERSION], []);
			$extra["handler_version"] = self::_EXTRA_VERSION;
		}

		// сравниваем версию пришедшей extra с текущей
		if ($extra["handler_version"] != self::_EXTRA_VERSION) {

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]           = array_merge(self::_EXTRA_SCHEME[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["handler_version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}