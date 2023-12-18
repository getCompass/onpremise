<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы с данными пользователя
 */
class Type_User_Main {

	// -------------------------------------------------------
	// USER TYPES VARIABLES
	// -------------------------------------------------------

	public const HUMAN              = "human";      // пользователь
	public const SYSTEM_BOT         = "systembot";  // системный бот
	public const SYSTEM_SUPPORT_BOT = "systemsupportbot";  // системный бот отдела поддержки
	public const SUPPORT_BOT        = "supportbot"; // бот поддержки
	public const OUTER_BOT          = "outerbot";   // внешний бот
	public const USER_BOT           = "userbot";    // пользовательский бот

	public const NPC_TYPE_HUMAN              = 1; // пользователь
	public const NPC_TYPE_SYSTEM_BOT_NOTICE  = 5; // тип системный бот подтип Оповещение
	public const NPC_TYPE_SYSTEM_BOT_REMIND  = 6; // тип системный бот подтип Напоминание
	public const NPC_TYPE_SYSTEM_BOT_SUPPORT = 7; // тип системный бот, подтип Отдел поддержки

	protected const _SYSTEM_BOT_NPC_TYPE_FROM = 5;
	protected const _SYSTEM_BOT_NPC_TYPE_TO   = 50;

	protected const _SUPPORT_BOT_NPC_TYPE_FROM = 51;
	protected const _SUPPORT_BOT_NPC_TYPE_TO   = 100;

	protected const _OUTER_BOT_NPC_TYPE_FROM = 101;
	protected const _OUTER_BOT_NPC_TYPE_TO   = 127;

	protected const _USER_BOT_NPC_TYPE_FROM = 128;
	protected const _USER_BOT_NPC_TYPE_TO   = 170;

	// список подтипов для системного бота
	protected const _SYSTEM_BOT_SUBTYPE = [
		self::NPC_TYPE_SYSTEM_BOT_NOTICE  => "notice_bot",
		self::NPC_TYPE_SYSTEM_BOT_REMIND  => "remind_bot",
		self::NPC_TYPE_SYSTEM_BOT_SUPPORT => "support_bot",
	];

	// -------------------------------------------------------
	// PUBLIC METHODS Определение типа пользователя
	// -------------------------------------------------------

	/**
	 * возвращает тип пользователя
	 *
	 * @param int $npc_type
	 *
	 * @return string
	 * @throws ParseFatalException
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

		throw new ParseFatalException("can't get user type");
	}

	/**
	 * возвращает npc-тип пользователя
	 *
	 * @param string $user_type
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function getNpcTypeByUserType(string $user_type):int {

		switch ($user_type) {

			case self::HUMAN:
				return self::NPC_TYPE_HUMAN;
			case self::USER_BOT:
				return self::getUserbotNpcType();
			case self::SYSTEM_BOT:
				return self::getSystemBotNpcType();
			case self::SYSTEM_SUPPORT_BOT:
				return self::NPC_TYPE_SYSTEM_BOT_SUPPORT;
			case self::SUPPORT_BOT:
				return self::getSupportBotNpcType();
			case self::OUTER_BOT:
				return self::getOuterBotNpcType();
		}

		throw new ParseFatalException("unknown user type");
	}

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
	 * получаем npc_type для системного бота
	 */
	public static function getSystemBotNpcType():int {

		return self::_SYSTEM_BOT_NPC_TYPE_FROM;
	}

	/**
	 * получаем npc_type для бота поддержки
	 */
	public static function getSupportBotNpcType():int {

		return self::_SUPPORT_BOT_NPC_TYPE_FROM;
	}

	/**
	 * получаем npc_type для внешнего бота
	 */
	public static function getOuterBotNpcType():int {

		return self::_OUTER_BOT_NPC_TYPE_FROM;
	}

	/**
	 * получаем npc_type для пользовательского бота
	 */
	public static function getUserbotNpcType():int {

		return self::_USER_BOT_NPC_TYPE_FROM;
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
}
