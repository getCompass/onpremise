<?php

namespace Compass\Conversation;

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

	/**
	 * список доступных типов пользователей
	 */
	protected const _ALLOWED_USER_TYPE_LIST = [
		self::USER_HUMAN,
		self::USER_SYSTEM_BOT,
		self::USER_SUPPORT_BOT,
		self::USER_OUTER_BOT,
		self::USER_BOT,
		self::OPERATOR,
	];

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

	// возвращает npc-тип пользователя
	public static function getNpcTypeByUserType(string $user_type):int {

		switch ($user_type) {

			case self::USER_HUMAN:
				return self::_NPC_TYPE_HUMAN;
			case self::USER_BOT:
				return self::getUserbotNpcType();
			case self::USER_SYSTEM_BOT:
				return self::getSystemBotNpcType();
			case self::USER_SUPPORT_BOT:
				return self::getSupportBotNpcType();
			case self::USER_OUTER_BOT:
				return self::getOuterBotNpcType();
		}

		throw new ParseFatalException("unknown user type");
	}

	/**
	 * проверяем что существуют данные типы пользователей из списка
	 *
	 * @param array $user_type_list
	 *
	 * @return void
	 * @throws \CompassApp\Domain\User\Exception\NotAllowedType
	 */
	public static function assertUserTypeList(array $user_type_list):void {

		foreach ($user_type_list as $user_type) {

			if (!in_array($user_type, self::_ALLOWED_USER_TYPE_LIST)) {
				throw new \CompassApp\Domain\User\Exception\NotAllowedType("user not allowed type");
			}
		}
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
}
