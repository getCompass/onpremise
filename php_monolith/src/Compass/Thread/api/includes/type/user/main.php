<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с go_usercache и таблицами user
 */
class Type_User_Main {

	// -------------------------------------------------------
	// USER TYPES VARIABLES
	// -------------------------------------------------------

	public const USER_HUMAN       = "human";
	public const USER_SYSTEM_BOT  = "systembot";
	public const USER_SUPPORT_BOT = "supportbot";
	public const USER_OUTER_BOT   = "outerbot";
	public const USER_BOT         = "userbot";
	public const OPERATOR         = "operator";

	protected const _NPC_TYPE_HUMAN            = 1; // пользователь
	protected const _SYSTEM_BOT_NPC_TYPE_FROM  = 5;
	protected const _SYSTEM_BOT_NPC_TYPE_TO    = 50;
	protected const _SUPPORT_BOT_NPC_TYPE_FROM = 51;
	protected const _SUPPORT_BOT_NPC_TYPE_TO   = 100;
	protected const _OUTER_BOT_NPC_TYPE_FROM   = 101;
	protected const _OUTER_BOT_NPC_TYPE_TO     = 127;
	protected const _USER_BOT_NPC_TYPE_FROM    = 128;
	protected const _USER_BOT_NPC_TYPE_TO      = 170;
	protected const _NPC_TYPE_OPERATOR         = 171; // оператор поддержки

	// -------------------------------------------------------
	// EXTRA VARIABLES
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 4; // текущая версия extra
	protected const _EXTRA_SCHEME  = [  // массив с версиями extra

		1 => [
			"is_email_attached" => 0, // привязан ли почтовый адрес у пользователя
			"is_disabled"       => 0, // флаг отвечающий за деактивацию аккаунт пользователя
		],

		2 => [
			"is_email_attached" => 0,  // привязан ли почтовый адрес у пользователя
			"is_disabled"       => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"badge"             => [
				"content"  => "",    // текст баджа
				"color_id" => 0,     // цвет баджа
			],
		],

		3 => [
			"is_email_attached" => 0,  // привязан ли почтовый адрес у пользователя
			"is_disabled"       => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"mbti_type"         => "",  // mbti тип личности
			"badge"             => [
				"content"  => "",    // текст баджа
				"color_id" => 0,     // цвет баджа
			],
		],

		4 => [
			"is_email_attached" => 0,  // привязан ли почтовый адрес у пользователя
			"is_disabled"       => 0,  // флаг отвечающий за деактивацию аккаунт пользователя
			"disabled_at"       => 0,  // время, показывающее когда деактировали аккаунт сотрудника
			"mbti_type"         => "",  // mbti тип личности
			"badge"             => [
				"content"  => "",    // текст баджа
				"color_id" => 0,     // цвет баджа
			],
		],
	];

	// -------------------------------------------------------
	// PUBLIC METHODS Определение типа пользователя
	// -------------------------------------------------------

	// возвращает тип пользователя
	public static function getUserType(int $npc_type):string {

		if (self::isHuman($npc_type)) {

			// человек
			return self::USER_HUMAN;
		} elseif (self::isSystemBot($npc_type)) {

			// системный бот
			return self::USER_SYSTEM_BOT;
		} elseif (self::isSupportBot($npc_type)) {

			// системный бот поддержки
			return self::USER_SUPPORT_BOT;
		} elseif (self::isOuterBot($npc_type)) {

			// внешний бот
			return self::USER_OUTER_BOT;
		} elseif (self::isUserbot($npc_type)) {

			// пользовательский бот
			return self::USER_BOT;
		} elseif (self::isOperator($npc_type)) {

			// оператор поддержки
			return self::OPERATOR;
		}

		throw new ParseFatalException("can't get user type");
	}

	// решаем, является ли пользователь человеком
	public static function isHuman(int $npc_type):bool {

		return $npc_type == self::_NPC_TYPE_HUMAN;
	}

	// решаем, является ли пользователь оператором
	public static function isOperator(int $npc_type):bool {

		return $npc_type == self::_NPC_TYPE_OPERATOR;
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
			|| ($npc_type > self::_NPC_TYPE_HUMAN && $npc_type < self::_SYSTEM_BOT_NPC_TYPE_FROM);
	}

	// решаем, является ли пользователь пользовательским ботом
	public static function isUserbot(int $npc_type):bool {

		return $npc_type >= self::_USER_BOT_NPC_TYPE_FROM && $npc_type <= self::_USER_BOT_NPC_TYPE_TO;
	}

	// проверяем, является ли пользователь ботом
	public static function isBot(int $npc_type):bool {

		return self::isSystemBot($npc_type) || self::isSupportBot($npc_type) || self::isOuterBot($npc_type);
	}

	// -------------------------------------------------------
	// PUBLIC METHODS OTHER
	// -------------------------------------------------------

	// проверяем, что у пользователя привязана почта
	public static function isEmailAttached(array $extra):bool {

		$is_without_email = Type_System_Legacy::isWithoutEmail();

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $is_without_email || $extra["extra"]["is_email_attached"] == 1;
	}

	// проверяем, активен ли аккаунт пользователя
	public static function isDisabledProfile(array $extra):bool {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_disabled"] == 1 ? true : false;
	}

	// получаем цвет badge
	public static function getBadgeColor(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		return $extra["extra"]["badge"]["color_id"];
	}

	// получаем content badge
	public static function getBadgeContent(array $extra):string {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		return $extra["extra"]["badge"]["content"];
	}

	// проверяем существует ли badge
	public static function isBadgeExist(array $extra):bool {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return isset($extra["extra"]["badge"]["content"]) && $extra["extra"]["badge"]["content"] != "";
	}

	// получаем время деактивации аккаунта
	public static function getProfileDisabledAt(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["disabled_at"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// актуализирует структуру extra
	protected static function _getExtra(array $extra):array {

		// сравниваем версию пришедшей extra с текущей
		if ($extra["handler_version"] != self::_EXTRA_VERSION) {

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]           = array_merge(self::_EXTRA_SCHEME[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["handler_version"] = self::_EXTRA_VERSION;
		}
		return $extra;
	}
}
