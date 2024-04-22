<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Константные значения пользователя
 */
class Type_User_Main {

	public const USER_HUMAN       = "human";
	public const USER_SYSTEM_BOT  = "systembot";
	public const USER_SUPPORT_BOT = "supportbot";
	public const USER_OUTER_BOT   = "outerbot";
	public const USER_BOT         = "userbot";
	public const OPERATOR         = "operator";

	public const NPC_TYPE_HUMAN              = 1;   // пользователь
	public const NPC_TYPE_BOT                = 2;   // бот
	public const NPC_TYPE_SYSTEM_BOT_NOTICE  = 5;   // тип системный бот, подтип Оповещение
	public const NPC_TYPE_SYSTEM_BOT_REMIND  = 6;   // тип системный бот, подтип Напоминание
	public const NPC_TYPE_SYSTEM_BOT_SUPPORT = 7;   // тип системный бот, подтип Служба поддержки
	public const NPC_TYPE_OUTER_BOT          = 101; // внешний бот
	public const NPC_TYPE_USER_BOT           = 128; // пользовательский бот
	public const NPC_TYPE_OPERATOR           = 171; // оператор поддержки

	protected const _SYSTEM_BOT_NPC_TYPE_FROM = 5;
	protected const _SYSTEM_BOT_NPC_TYPE_TO   = 50;

	protected const _SUPPORT_BOT_NPC_TYPE_FROM = 51;
	protected const _SUPPORT_BOT_NPC_TYPE_TO   = 100;

	protected const _OUTER_BOT_NPC_TYPE_FROM = 101;
	protected const _OUTER_BOT_NPC_TYPE_TO   = 127;

	protected const _USER_BOT_NPC_TYPE_FROM = 128;
	protected const _USER_BOT_NPC_TYPE_TO   = 170;

	// массив для преобразования внутреннего типа во внешний
	public const USER_TYPE_SCHEMA = [
		self::USER_HUMAN       => "user",
		self::USER_SYSTEM_BOT  => "system_bot",
		self::USER_SUPPORT_BOT => "support_bot",
		self::USER_OUTER_BOT   => "bot",
		self::USER_BOT         => "userbot",
		self::OPERATOR         => "operator",
	];

	// -------------------------------------------------------
	// EXTRA VARIABLES
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 8; // текущая версия extra
	protected const _EXTRA_SCHEME  = [  // массив с версиями extra

		8 => [
			"is_disabled"                              => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"                              => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"discount_percent"                         => 0,  // процент скидки при оплате плана
			"partner_fee_percent"                      => 0,  // процент доли, которую получает партнер от оплат собственника за план
			"user_disabled_analytics_event_group_list" => [], // список логов, отключенные для пользователя
			"avg_screen_time"                          => 0,  // среднее экранное время пользователя
			"total_action_count"                       => 0,  // общее количество действий пользователя
			"avg_message_answer_time"                  => 0,  // среднее время ответа на сообщения
			"avatar_color_id"                          => 0,  // id аватара пользователя
			"onboarding_list"                          => [], // список онбордингов
		],
	];

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// решаем, является ли пользователь человеком
	public static function isHuman(int $npc_type):bool {

		return $npc_type == self::NPC_TYPE_HUMAN;
	}

	// решаем, является ли пользователь системным ботом
	public static function isSystemBot(int $npc_type):bool {

		return $npc_type >= self::_SYSTEM_BOT_NPC_TYPE_FROM && $npc_type <= self::_SYSTEM_BOT_NPC_TYPE_TO;
	}

	// решаем, является ли пользователь ботом поддержки
	public static function isSupportBot(int $npc_type):bool {

		return $npc_type >= self::_SUPPORT_BOT_NPC_TYPE_FROM && $npc_type <= self::_SUPPORT_BOT_NPC_TYPE_TO;
	}

	// решаем, является ли пользователь внешним ботом
	public static function isOuterBot(int $npc_type):bool {

		return ($npc_type >= self::_OUTER_BOT_NPC_TYPE_FROM && $npc_type <= self::_OUTER_BOT_NPC_TYPE_TO)
			|| ($npc_type > self::NPC_TYPE_HUMAN && $npc_type < self::_SYSTEM_BOT_NPC_TYPE_FROM);
	}

	// решаем, является ли пользователь пользовательским ботом
	public static function isUserbot(int $npc_type):bool {

		return $npc_type >= self::_USER_BOT_NPC_TYPE_FROM && $npc_type <= self::_USER_BOT_NPC_TYPE_TO;
	}

	// проверяем, является ли пользователь ботом
	public static function isBot(int $npc_type):bool {

		return self::isSystemBot($npc_type) || self::isSupportBot($npc_type) || self::isOuterBot($npc_type) || self::isUserbot($npc_type);
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