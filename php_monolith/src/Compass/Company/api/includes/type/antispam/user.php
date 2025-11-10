<?php

namespace Compass\Company;

/**
 * Класс для блокировки пользователя
 */
class Type_Antispam_User extends Type_Antispam_Main {

	##########################################################
	# region константы для блокировок
	##########################################################

	const PIN_CODE_LIMIT = [
		"key"    => "PIN_CODE_LIMIT",
		"limit"  => 3,
		"expire" => 10 * 60,
	];

	const OLD_PIN_CODE_LIMIT = [
		"key"    => "OLD_PIN_CODE_LIMIT",
		"limit"  => 3,
		"expire" => 10 * 60,
	];

	const COMPANY_SET_PROFILE = [
		"key"    => "COMPANY_SET_PROFILE",
		"limit"  => 10,
		"expire" => 60,
	];

	const COMPANY_CLEAR_AVATAR = [
		"key"    => "COMPANY_CLEAR_AVATAR",
		"limit"  => 10,
		"expire" => 60,
	];

	const COMPANY_SET_GENERAL_CHAT_NOTIFICATIONS = [
		"key"    => "COMPANY_SET_GENERAL_CHAT_NOTIFICATIONS",
		"limit"  => 10,
		"expire" => 60,
	];

	const COMPANY_SET_UNLIMITED_MESSAGES_EDITING = [
		"key"    => "COMPANY_SET_UNLIMITED_MESSAGES_EDITING",
		"limit"  => 10,
		"expire" => 60,
	];

	const COMPANY_SET_UNLIMITED_MESSAGES_DELETING = [
		"key"    => "COMPANY_SET_UNLIMITED_MESSAGES_DELETING",
		"limit"  => 10,
		"expire" => 60,
	];

	const COMPANY_SET_LOCAL_LINKS = [
		"key"    => "COMPANY_SET_LOCAL_LINKS",
		"limit"  => 10,
		"expire" => 60,
	];

	const REQUIRE_PIN_CODE_COMPANY = [
		"key"    => "REQUIRE_PIN_CODE_COMPANY",
		"limit"  => 3,
		"expire" => HOUR1,
	];

	const PROFILE_SETEMPLOYEEPLAN = [
		"key"    => "PROFILE_SETEMPLOYEEPLAN",
		"limit"  => 10,
		"expire" => 60,
	];

	const PROFILE_SETDESCRIPTION = [
		"key"    => "PROFILE_SETDESCRIPTION",
		"limit"  => 100,
		"expire" => 3 * 60 * 60,
	];

	const PROFILE_SETSTATUS = [
		"key"    => "PROFILE_SETSTATUS",
		"limit"  => 10,
		"expire" => 60 * 60,
	];

	const PROFILE_SETMBTITYPE = [
		"key"    => "PROFILE_SETMBTITYPE",
		"limit"  => 10,
		"expire" => 60 * 60,
	];

	const PROFILE_SETBADGE = [
		"key"    => "PROFILE_SETBADGE",
		"limit"  => 100,
		"expire" => 3 * 60 * 60,
	];

	const PROFILE_SETJOINTIME = [
		"key"    => "PROFILE_SETJOINTIME",
		"limit"  => 10,
		"expire" => 60 * 60,
	];

	const PROFILE_SETPROFILE = [
		"key"    => "PROFILE_SETPROFILE",
		"limit"  => 100,
		"expire" => 3 * 60 * 60,
	];

	// -------------------------------------------------------
	// achievement
	// -------------------------------------------------------

	const ACHIEVEMENT_ADD = [
		"key"    => "ACHIEVEMENT_ADD",
		"limit"  => 10,
		"expire" => 60,
	];

	const ACHIEVEMENT_EDIT = [
		"key"    => "ACHIEVEMENT_EDIT",
		"limit"  => 10,
		"expire" => 60,
	];

	const ACHIEVEMENT_REMOVE = [
		"key"    => "ACHIEVEMENT_REMOVE",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// card_editor
	// -------------------------------------------------------

	const EDITOR_ADD = [
		"key"    => "EDITOR_ADD",
		"limit"  => 30,
		"expire" => 60,
	];

	const EDITOR_ADD_BATCHING = [
		"key"    => "EDITOR_ADD_BATCHING",
		"limit"  => 10,
		"expire" => 60 * 5,
	];

	const EDITOR_REMOVE = [
		"key"    => "EDITOR_REMOVE",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// respect
	// -------------------------------------------------------

	const RESPECT_ADD = [
		"key"    => "RESPECT_ADD",
		"limit"  => 10,
		"expire" => 60,
	];

	const RESPECT_EDIT = [
		"key"    => "RESPECT_EDIT",
		"limit"  => 10,
		"expire" => 60,
	];

	const RESPECT_REMOVE = [
		"key"    => "RESPECT_REMOVE",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// sprint
	// -------------------------------------------------------

	const SPRINT_ADD = [
		"key"    => "SPRINT_ADD",
		"limit"  => 5,
		"expire" => 60,
	];

	const SPRINT_EDIT = [
		"key"    => "SPRINT_EDIT",
		"limit"  => 10,
		"expire" => 60,
	];

	const SPRINT_REMOVE = [
		"key"    => "SPRINT_REMOVE",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// loyalty
	// -------------------------------------------------------

	const LOYALTY_ADD = [
		"key"    => "LOYALTY_ADD",
		"limit"  => 5,
		"expire" => 60,
	];

	const LOYALTY_EDIT = [
		"key"    => "LOYALTY_EDIT",
		"limit"  => 10,
		"expire" => 60,
	];

	const LOYALTY_REMOVE = [
		"key"    => "LOYALTY_REMOVE",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// worked_hours
	// -------------------------------------------------------

	const WORKEDHOURS_EDIT = [
		"key"    => "WORKEDHOURS_EDIT",
		"limit"  => 8,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// hiring //
	// -------------------------------------------------------

	const HIRING_REQUEST_CONFIRM = [
		"key"    => "HIRING_REQUEST_CONFIRM",
		"limit"  => 100,
		"expire" => 60 * 10,
	];

	const HIRING_REQUEST_REJECT = [
		"key"    => "HIRING_REQUEST_REJECT",
		"limit"  => 100,
		"expire" => 60 * 10,
	];

	const DISMISSAL_REQUEST_CREATE = [
		"key"    => "DISMISSAL_REQUEST_CREATE",
		"limit"  => 100,
		"expire" => 60 * 10,
	];

	const DISMISSAL_REQUEST_APPROVE = [
		"key"    => "DISMISSAL_REQUEST_APPROVE",
		"limit"  => 100,
		"expire" => 60 * 10,
	];

	const DISMISSAL_REQUEST_CREATE_AND_APPROVE = [
		"key"    => "DISMISSAL_REQUEST_CREATE_AND_APPROVE",
		"limit"  => 100,
		"expire" => 60 * 10,
	];

	const DISMISSAL_REQUEST_REJECT = [
		"key"    => "DISMISSAL_REQUEST_REJECT",
		"limit"  => 100,
		"expire" => 60 * 10,
	];

	// -------------------------------------------------------
	// role and groups
	// -------------------------------------------------------

	const SET_ROLE = [
		"key"    => "SET_ROLE",
		"limit"  => 10,
		"expire" => 60,
	];

	const ADD_GROUP = [
		"key"    => "ADD_GROUP",
		"limit"  => 10,
		"expire" => 60,
	];

	const DELETE_FROM_GROUP = [
		"key"    => "DELETE_FROM_GROUP",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// hiring_conversation_preset
	// -------------------------------------------------------

	const CONVERSATION_PRESET_CREATE_REQUEST = [
		"key"    => "CONVERSATION_PRESET_CREATE_REQUEST",
		"limit"  => 5,
		"expire" => 60,
	];

	const CONVERSATION_PRESET_UPDATE_TITLE_REQUEST = [
		"key"    => "CONVERSATION_PRESET_UPDATE_TITLE_REQUEST",
		"limit"  => 10,
		"expire" => 60,
	];

	const CONVERSATION_PRESET_UPDATE_SET_REQUEST = [
		"key"    => "CONVERSATION_PRESET_UPDATE_SET_REQUEST",
		"limit"  => 5,
		"expire" => 60,
	];

	const CONVERSATION_PRESET_DELETE_REQUEST = [
		"key"    => "CONVERSATION_PRESET_DELETE_REQUEST",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// config
	// -------------------------------------------------------

	const PUSH_BODY_DISPLAY = [
		"key"    => "PUSH_BODY_DISPLAY",
		"limit"  => 10,
		"expire" => 60,
	];

	const EXTENDED_EMPLOYEE_CARD = [
		"key"    => "EXTENDED_EMPLOYEE_CARD",
		"limit"  => 5,
		"expire" => HOUR1,
	];

	const SWITCH_PREMIUM_PAYMENT_REQUESTING_CONFIG = [
		"key"    => "SWITCH_PREMIUM_PAYMENT_REQUESTING_CONFIG",
		"limit"  => 10,
		"expire" => 60,
	];

	const SET_MEMBER_PERMISSIONS = [
		"key"    => "SET_MEMBER_PERMISSIONS",
		"limit"  => 5,
		"expire" => HOUR1,
	];

	const SET_ADD_TO_GENERAL_CHAT_ON_HIRING = [
		"key"    => "SET_ADD_TO_GENERAL_CHAT_ON_HIRING",
		"limit"  => 10,
		"expire" => 60,
	];

	const SET_SHOW_MESSAGE_READ_STATUS = [
		"key"    => "SET_SHOW_MESSAGE_READ_STATUS",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// join link
	// -------------------------------------------------------

	const CREATE_JOIN_LINK = [
		"key"    => "CREATE_JOIN_LINK",
		"limit"  => 50,
		"expire" => HOUR1,
	];

	const EDIT_JOIN_LINK = [
		"key"    => "EDIT_JOIN_LINK",
		"limit"  => 10,
		"expire" => 60,
	];

	const DELETE_JOIN_LINK = [
		"key"    => "DELETE_JOIN_LINK",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// notifications
	// -------------------------------------------------------

	const NOTIFICATION_TOGGLE = [
		"key"    => "NOTIFICATION_TOGGLE",
		"limit"  => 10,
		"expire" => 60,
	];

	const NOTIFICATION_FOREVENTTOGGLE = [
		"key"    => "NOTIFICATION_FOREVENTTOGGLE",
		"limit"  => 30,
		"expire" => 60,
	];

	const NOTIFICATION_ADDDEVICE = [
		"key"    => "NOTIFICATION_ADDDEVICE",
		"limit"  => 10,
		"expire" => 60 * 60,
	];

	// -------------------------------------------------------
	// region userbot
	// -------------------------------------------------------

	const USERBOT_CREATE = [
		"key"    => "USERBOT_CREATE",
		"limit"  => 10,
		"expire" => 20 * 60,
	];

	const USERBOT_DISABLE = [
		"key"    => "USERBOT_DISABLE",
		"limit"  => 5,
		"expire" => 60,
	];

	const USERBOT_DELETE = [
		"key"    => "USERBOT_DELETE",
		"limit"  => 10,
		"expire" => 20 * 60,
	];

	const USERBOT_ENABLE = [
		"key"    => "USERBOT_ENABLE",
		"limit"  => 5,
		"expire" => 60,
	];

	const USERBOT_EDIT = [
		"key"    => "USERBOT_EDIT",
		"limit"  => 20,
		"expire" => 20 * 60,
	];

	const USERBOT_REFRESH_SECRET_KEY = [
		"key"    => "USERBOT_REFRESH_SECRET_KEY",
		"limit"  => 15,
		"expire" => 10 * 60,
	];

	const USERBOT_NOT_FOUND = [
		"key"    => "USERBOT_NOT_FOUND",
		"limit"  => 5,
		"expire" => HOUR1,
	];

	const USERBOT_ADD_TO_GROUP = [
		"key"    => "USERBOT_ADD_TO_GROUP",
		"limit"  => 20,
		"expire" => 5 * 60,
	];

	const USERBOT_REMOVE_FROM_GROUP = [
		"key"    => "USERBOT_REMOVE_FROM_GROUP",
		"limit"  => 15,
		"expire" => 5 * 60,
	];

	// endregion userbot
	// -------------------------------------------------------

	// -------------------------------------------------------
	// region smart_app
	// -------------------------------------------------------

	const SMART_APP_CREATE = [
		"key"    => "SMART_APP_CREATE",
		"limit"  => 20,
		"expire" => 5 * 60,
	];

	const SMART_APP_DELETE = [
		"key"    => "SMART_APP_DELETE",
		"limit"  => 10,
		"expire" => 5 * 60,
	];

	const SMART_APP_EDIT = [
		"key"    => "SMART_APP_EDIT",
		"limit"  => 20,
		"expire" => 5 * 60,
	];

	const SMART_APP_REFRESH_KEYS = [
		"key"    => "SMART_APP_REFRESH_KEYS",
		"limit"  => 15,
		"expire" => 10 * 60,
	];

	// endregion userbot
	// -------------------------------------------------------

	// -------------------------------------------------------
	// region remind
	// -------------------------------------------------------

	const REMIND_SEND_MESSAGE = [
		"key"    => "REMIND_SEND_MESSAGE",
		"limit"  => 15,
		"expire" => 60,
	];

	// endregion remind

	// -------------------------------------------------------
	// region premium
	// -------------------------------------------------------

	const PREMIUM_PAYMENT_REQUEST_READ_ALL = [
		"key"    => "PREMIUM_PAYMENT_REQUEST_READ_ALL",
		"limit"  => 60,
		"expire" => 60 * 5,
	];

	// endregion remind
	// -------------------------------------------------------

	// -------------------------------------------------------
	// region permissions
	// -------------------------------------------------------

	const SET_PERMISSIONS = [
		"key"    => "SET_PERMISSIONS",
		"limit"  => 10,
		"expire" => 60,
	];

	const SET_PERMISSIONS_PROFILE_CARD = [
		"key"    => "SET_PERMISSIONS_PROFILE_CARD",
		"limit"  => 100,
		"expire" => HOUR1 * 3,
	];

	const UPGRADE_GUEST = [
		"key"    => "UPGRADE_GUEST",
		"limit"  => 10,
		"expire" => 60,
	];

	// endregion permissions
	// -------------------------------------------------------

	// -------------------------------------------------------
	// region tariff
	// -------------------------------------------------------

	const SHOWCASE_OPENED = [
		"key"    => "SHOWCASE_OPENED",
		"limit"  => 1,
		"expire" => 60 * 15,
	];

	// endregion tariff
	// -------------------------------------------------------

	# endregion константы для блокировок
	##########################################################

	protected const _DB_KEY    = "company_system";
	protected const _TABLE_KEY = "antispam_user";

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	// проверяем на срабатывание блокировок по конкретному ключу
	// пишем статистику по срабатыванию блокировки если необходимо
	public static function throwIfBlocked(int $user_id, array $block_key):void {

		if (self::needCheckIsBlocked()) {
			return;
		}

		// получаем текущее состояние блокировки
		$row = self::_getRow($user_id, $block_key);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {
			throw new \BaseFrame\Exception\Request\BlockException("User with user_id [{$user_id}] blocked with key: '{$block_key["key"]}'", $row["expires_at"]);
		}

		// обновляем запись
		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expires_at"]);
	}

	// установить значение блокировки метода
	public static function setBlockValue(int $user_id, string $block_key, int $value):void {

		$block_key = "self::$block_key";
		$block_key = constant($block_key);

		// получаем текущее состояние блокировки
		$row = self::_getRow($user_id, $block_key);

		// обновляем запись
		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $value, $row["expires_at"]);
	}

	// получаем состояние блокировки
	protected static function _getRow(int $user_id, array $block_key):array {

		// получаем запись с блокировкой из базы
		$row = self::_get($user_id, $block_key["key"]);

		// если время превысило expires_at, то сбрасываем блокировку
		if (time() > $row["expires_at"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expires_at"]   = time() + $block_key["expire"];
		}

		return $row;
	}

	// узнать сработала ли блокировка без инкремента
	public static function assertKeyIsNotBlocked(int $user_id, array $block_key, array $row = []):void {

		// получаем текущее состояние блокировки
		if (count($row) < 1) {
			$row = self::_get($user_id, $block_key["key"]);
		}

		// если время действие активно и превысили лимит - блокировка сработала
		if (self::_isBlock($row, $block_key["limit"])) {
			throw new \BaseFrame\Exception\Request\BlockException("Company [{$user_id}] blocked with key: '{$block_key["key"]}'", $row["expires_at"]);
		}
	}

	/**
	 * проверяем сработала ли блокировка
	 */
	protected static function _isBlock(array $row, int $block_key_limit):bool {

		// если время действие неактивно
		if (time() > $row["expires_at"]) {
			return false;
		}

		// если не дошли до лимита
		if ($row["count"] < $block_key_limit) {
			return false;
		}
		return true;
	}

	/**
	 * Очистка всех блокировок, если передан пользователь то чистим блокировки для переданного пользователя
	 */
	public static function clearAll(int $user_id = 0):void {

		// проверяем выполняется ли код на тестовом сервере/стейдже
		assertNotPublicServer();

		// формируем стандартный запрос для удаления всех блокировок
		$query = "DELETE FROM `?p` WHERE ?i = ?i LIMIT ?i";
		$args  = [1, 1, 1000000];

		// если передан пользователь - формируем запрос на удаление
		if ($user_id > 0) {

			$query = "DELETE FROM `?p` WHERE user_id = ?i LIMIT ?i";
			$args  = [$user_id, 1000000];
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, ...$args);
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	// создаем новую или обновляем существующую запись в базе
	protected static function _set(int $user_id, string $key, int $is_stat_sent, int $count, int $expires_at):void {

		$set = [
			"user_id"      => $user_id,
			"key"          => $key,
			"is_stat_sent" => $is_stat_sent,
			"count"        => $count,
			"expires_at"   => $expires_at,
		];

		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, $set);
	}

	// пытаемся получить информацию по ключу и user_id
	protected static function _get(int $user_id, string $key):array {

		$row = ShardingGateway::database(self::_DB_KEY)
			->getOne("SELECT * FROM `?p` WHERE user_id=?s AND `key`=?s LIMIT ?i", self::_TABLE_KEY, $user_id, $key, 1);

		// если записи нет - формируем
		if (!isset($row["user_id"])) {

			$row = [
				"user_id"      => $user_id,
				"key"          => $key,
				"is_stat_sent" => 0,
				"count"        => 0,
				"expires_at"   => 0,
			];
		}

		return $row;
	}
}
