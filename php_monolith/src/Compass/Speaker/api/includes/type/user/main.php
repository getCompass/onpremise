<?php

namespace Compass\Speaker;

/**
 * класс для работы с go_usercache и таблицами user
 */
class Type_User_Main {

	// -------------------------------------------------------
	// USER TYPES VARIABLES
	// -------------------------------------------------------

	public const USER_ROLE_KICKED  = 0; // статус пользователя в компании - уволенный
	public const USER_ROLE_DEFAULT = 1; // статус пользователя в компании - рядовой сотрудник
	public const USER_ROLE_LEADER  = 2; // статус пользователя в компании - руководитель
	public const USER_ROLE_OWNER   = 3; // статус пользователя в компании - владелец компании

	public const USER_HUMAN       = "human";
	public const USER_SYSTEM_BOT  = "systembot";
	public const USER_SUPPORT_BOT = "supportbot";
	public const USER_OUTER_BOT   = "outerbot";

	protected const _NPC_TYPE_HUMAN            = 1; // пользователь
	protected const _SYSTEM_BOT_NPC_TYPE_FROM  = 5;
	protected const _SYSTEM_BOT_NPC_TYPE_TO    = 50;
	protected const _SUPPORT_BOT_NPC_TYPE_FROM = 51;
	protected const _SUPPORT_BOT_NPC_TYPE_TO   = 100;
	protected const _OUTER_BOT_NPC_TYPE_FROM   = 101;
	protected const _OUTER_BOT_NPC_TYPE_TO     = 127;

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
		}

		throw new \parseException("can't get user type");
	}

	// решаем, является ли пользователь человеком
	public static function isHuman(int $npc_type):bool {

		return $npc_type == self::_NPC_TYPE_HUMAN;
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

	// проверяем, является ли пользователь ботом
	public static function isBot(int $npc_type):bool {

		return self::isSystemBot($npc_type) || self::isSupportBot($npc_type) || self::isOuterBot($npc_type);
	}

	// проверяем, является ли пользователь ботом
	public static function isDisabledProfile(int $role):bool {

		return $role == self::USER_ROLE_KICKED;
	}

	// получаем незаблокированных в системе пользователей
	public static function getNotDisabledUsers(array $user_info_list, bool $is_need_grouped = false):array {

		$not_disabled_user_list = [];
		foreach ($user_info_list as $v) {

			// если пользователь заблокирован, то пропускаем
			if (self::isDisabledProfile($v->role)) {
				continue;
			}

			if ($is_need_grouped) {
				$not_disabled_user_list[$v->user_id] = $v;
			}
			$not_disabled_user_list[] = $v;
		}
		return $not_disabled_user_list;
	}

	/**
	 * получаем  npc_type для системного бота
	 *
	 * @throws \Exception
	 */
	public static function getSystemBotNpcType():int {

		return self::_SYSTEM_BOT_NPC_TYPE_FROM;
	}
}
