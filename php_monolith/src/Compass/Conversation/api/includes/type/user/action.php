<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс, отвечающий за описание действий пользователей и их доступ для отдельных типов пользователей
 */
class Type_User_Action {

	// действия, связанные с пользователями
	public const REGISTER_USER          = "REGISTER_USER";
	public const BLOCK_USER             = "BLOCK_USER";
	public const CREATE_SINGLE          = "CREATE_SINGLE";
	public const ATTACH_TO_BOT_LIST     = "ATTACH_TO_BOT_LIST";
	public const ATTACH_TO_USERBOT_LIST = "ATTACH_TO_USERBOT_LIST";

	// массив с действиями, которые доступны ДЛЯ пользователей этого типа
	protected const _ALLOWED_ACTIONS = [
		self::CREATE_SINGLE => [
			// эти пользователи могут создавать сингл-диалог
			Type_User_Main::USER_HUMAN,
			Type_User_Main::USER_SYSTEM_BOT,
			Type_User_Main::USER_SUPPORT_BOT,
			Type_User_Main::USER_BOT,
		],
	];

	// коды ошибок, которые нужно возвращаться при невозможности вполнения действия пользователм
	protected const _ALLOWED_ACTIONS_ERROR_CODES = [
		"DEFAULT" => 400,
	];

	// массив с действиями, которые доступны НАД пользователем этого типа
	protected const _ALLOWED_FOR_ACTIONS = [
		self::REGISTER_USER          => [
			// эти типы пользователей подходят для регистрации в системе
			Type_User_Main::USER_HUMAN,
			Type_User_Main::USER_SYSTEM_BOT,
			Type_User_Main::USER_SUPPORT_BOT,
			Type_User_Main::USER_OUTER_BOT,
		],
		self::BLOCK_USER             => [
			// этих пользователей можно блокировать
			Type_User_Main::USER_HUMAN,
			Type_User_Main::USER_OUTER_BOT,
		],
		self::CREATE_SINGLE          => [
			// сингл-диалог с этими пользователями можно создать
			Type_User_Main::USER_HUMAN,
			Type_User_Main::USER_OUTER_BOT,
			Type_User_Main::USER_BOT,
		],
		self::ATTACH_TO_BOT_LIST     => [
			// типы пользователей, которых нужно прицепить к списку ботов в диалоге
			Type_User_Main::USER_SYSTEM_BOT,
			Type_User_Main::USER_SUPPORT_BOT,
			Type_User_Main::USER_OUTER_BOT,
		],
		self::ATTACH_TO_USERBOT_LIST => [
			// типы пользователей, которых нужно прицепить к списку ботов в диалоге
			Type_User_Main::USER_BOT,
		],
	];

	// коды ошибок, которые нужно возвращаться при невозможности вполнения действия над пользователем
	protected const _ALLOWED_FOR_ACTIONS_ERROR_CODES = [
		"DEFAULT" => 400,
	];

	// ------------------------------------------------------------------------
	// PUBLIC METHODS Определение возможности совершения действия пользователем
	// ------------------------------------------------------------------------

	// проверяем, может пользователь совершать опреденное действия
	public static function isAbleToPerform(int $npc_type, string $action):bool {

		// проверяем валидность самого действия
		$action = strtoupper($action);
		if (!isset(self::_ALLOWED_ACTIONS[$action])) {
			throw new ParseFatalException("incorrect action");
		}

		// получаем тип пользователя
		$user_type = Type_User_Main::getUserType($npc_type);

		// смотрим, доступно ли это действия для этого пользователя
		return in_array($user_type, self::_ALLOWED_ACTIONS[$action]);
	}

	// возвращает код ошибки невозможности выполнения пользователем какого-либо дейтвия
	public static function getIsAbleToPerformErrorCode(string $action):int {

		return self::_ALLOWED_ACTIONS_ERROR_CODES[$action] ?? self::_ALLOWED_ACTIONS_ERROR_CODES["DEFAULT"];
	}

	// ----------------------------------------------------------------------------
	// PUBLIC METHODS Определение возможности совершения действия НАД пользователем
	// ----------------------------------------------------------------------------

	// проверяем, можно ли совершить над пользователем опреденное действия
	public static function isValidForAction(int $npc_type, string $action):bool {

		// проверяем валидность самого действия
		$action = strtoupper($action);
		if (!isset(self::_ALLOWED_FOR_ACTIONS[$action])) {
			throw new ParseFatalException("incorrect action");
		}

		// получаем тип пользователя
		$user_type = Type_User_Main::getUserType($npc_type);

		// смотрим, доступно ли это действия для этого пользователя
		return in_array($user_type, self::_ALLOWED_FOR_ACTIONS[$action]);
	}

	// возвращает код ошибки невозможности выполнения пользователем какого-либо дейтвия
	public static function getIsValidForActionErrorCode(string $action):int {

		return self::_ALLOWED_FOR_ACTIONS_ERROR_CODES[$action] ?? self::_ALLOWED_FOR_ACTIONS_ERROR_CODES["DEFAULT"];
	}
}