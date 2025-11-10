<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Константные значения пользователя
 */
class Type_User_Main {

	public const NPC_TYPE_HUMAN      = 1; // пользователь
	public const NPC_TYPE_BOT        = 2; // бот
	public const NPC_TYPE_SYSTEM_BOT = 5; // системный бот
	public const NPC_TYPE_OUTER_BOT  = 101; // внешний бот
	public const NPC_TYPE_USER_BOT   = 128; // пользовательский бот

	public const HUMAN       = "human";
	public const SYSTEM_BOT  = "systembot";
	public const SUPPORT_BOT = "supportbot";
	public const OUTER_BOT   = "outerbot";
	public const USER_BOT    = "userbot";

	public const DEFAULT_SHORT_DESCRIPTION = "Пользователь Compass";

	protected const _SYSTEM_BOT_NPC_TYPE_FROM  = 5;
	protected const _SYSTEM_BOT_NPC_TYPE_TO    = 50;
	protected const _SUPPORT_BOT_NPC_TYPE_FROM = 51;
	protected const _SUPPORT_BOT_NPC_TYPE_TO   = 100;
	protected const _OUTER_BOT_NPC_TYPE_FROM   = 101;
	protected const _OUTER_BOT_NPC_TYPE_TO     = 127;

	protected const _USER_BOT_NPC_TYPE_FROM = 128;
	protected const _USER_BOT_NPC_TYPE_TO   = 170;

	// -------------------------------------------------------
	// EXTRA VARIABLES
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 2; // текущая версия extra
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
			"is_disabled"         => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"         => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"discount_percent"    => 0, // процент скидки при оплате плана
			"partner_fee_percent" => 0, // процент доли, которую получает партнер от оплат собственника за план
		],
	];

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
		} elseif (self::isSystemBot($npc_type)) {

			// системный бот
			return self::SYSTEM_BOT;
		} elseif (self::isSupportBot($npc_type)) {

			// системный бот поддержки
			return self::SUPPORT_BOT;
		} elseif (self::isOuterBot($npc_type)) {

			// внешний бот
			return self::OUTER_BOT;
		} elseif (self::isUserBot($npc_type)) {

			// пользовательский бот
			return self::USER_BOT;
		}

		throw new ParseFatalException("can't get user type — unexpected npc type $npc_type");
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
	public static function setProfileDisabled(array $extra, ?int $disabled_at = null):array {

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