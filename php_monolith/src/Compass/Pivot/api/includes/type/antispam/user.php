<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;

/**
 * класс для блокировки пользователя по User
 */
class Type_Antispam_User extends Type_Antispam_Main {

	##########################################################
	# region константы для блокировок
	##########################################################

	const PROFILE_SET = [
		"key"    => "PROFILE_SET",
		"limit"  => 15,
		"expire" => HOUR1,
	];

	const WRONG_TWO_FA_TOKEN = [
		"key"    => "WRONG_TWO_FA_TOKEN",
		"limit"  => 2,
		"expire" => 5 * 60, // пять минут
	];

	const WRONG_MAIL_PASSWORD_TOKEN = [
		"key"    => "WRONG_MAIL_PASSWORD_TOKEN",
		"limit"  => 7,
		"expire" => 20 * 60, // 20 минут
	];

	const PROFILE_DELETE = [
		"key"    => "PROFILE_DELETE",
		"limit"  => 5,
		"expire" => DAY1,
	];

	// -------------------------------------------------------
	// company
	// -------------------------------------------------------

	const COMPANY_ADD = [
		"key"    => "COMPANY_ADD",
		"limit"  => 5,
		"expire" => DAY1,
	];

	const TRY_COMPANY_DELETE = [
		"key"    => "TRY_COMPANY_DELETE",
		"limit"  => 2 * 2, // по факту блокировка на 2 удаления, так как для полноценной работы метода требуется 2 вызова метода: вызывать 2fa и подтвердить 2fa
		"expire" => 5 * 60,
	];

	const COMPANY_DELETE = [
		"key"    => "COMPANY_DELETE",
		"limit"  => 5,
		"expire" => DAY1,
	];

	const COMPANY_SET_AVATAR = [
		"key"    => "COMPANY_SET_AVATAR",
		"limit"  => 20,
		"expire" => 1 * 60,
	];

	const COMPANY_SET_NAME = [
		"key"    => "COMPANY_SET_NAME",
		"limit"  => 30,
		"expire" => DAY1,
	];

	const COMPANY_SET_POSITION = [
		"key"    => "COMPANY_SET_POSITION",
		"limit"  => 30,
		"expire" => 5 * 60, // пять минут
	];

	const JOIN_LINK_VALIDATE = [
		"key"    => "JOIN_LINK_VALIDATE",
		"limit"  => 10,
		"expire" => HOUR2,
	];

	const CONFIRMATION_SELF_DISMISSAL_TYPE = [
		"key"    => "CONFIRMATION_FOR_TYPE",
		"limit"  => 5,
		"expire" => 50 * 60, // 50 минут
	];

	// -------------------------------------------------------
	// notification
	// -------------------------------------------------------

	const NOTIFICATION_ADDTOKEN = [
		"key"    => "NOTIFICATION_ADDTOKEN",
		"limit"  => 10,
		"expire" => 60 * 60,
	];

	const NOTIFICATION_TOGGLE = [
		"key"    => "NOTIFICATION_TOGGLE",
		"limit"  => 15,
		"expire" => 60 * 1,
	];

	const NOTIFICATION_FOREVENTTOGGLE = [
		"key"    => "NOTIFICATION_FOREVENTTOGGLE",
		"limit"  => 30,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// phone
	// -------------------------------------------------------

	const PHONE_CHANGE = [
		"key"    => "PHONE_CHANGE",
		"limit"  => 3,
		"expire" => DAY1,
	];

	##########################################################
	# invite code
	##########################################################

	const INVITECODE_ADD = [
		"key"    => "INVITECODE_ADD",
		"limit"  => 5,
		"expire" => HOUR1,
	];

	# endregion
	##########################################################

	##########################################################
	# premium
	##########################################################

	const PREMIUM_READ_NOTIFICATION = [
		"key"    => "PREMIUM_READ_NOTIFICATION",
		"limit"  => 30,
		"expire" => HOUR1,
	];

	const PREMIUM_USE_PROMO = [
		"key"    => "PREMIUM_USE_PROMO",
		"limit"  => 10,
		"expire" => HOUR1,
	];

	const PREMIUM_MAKE_BASKET = [
		"key"    => "PREMIUM_MAKE_BASKET",
		"limit"  => 60,
		"expire" => HOUR1,
	];

	# endregion
	##########################################################

	##########################################################
	# tariff
	##########################################################

	const VALIDATE_LICENSE_KEY = [
		"key"    => "VALIDATE_LICENSE_KEY",
		"limit"  => 10,
		"expire" => 30 * 60, // 30 минут
	];

	const ACTIVATE_LICENSE_KEY = [
		"key"    => "ACTIVATE_LICENSE_KEY",
		"limit"  => 2,
		"expire" => HOUR1 * 2, // 2 часа
	];

	# endregion
	##########################################################

	##########################################################
	# region auth
	##########################################################

	const AUTH_ON_REGISTRATION = [
		"key"    => "AUTH_ON_REGISTRATION",
		"limit"  => 1,
		"expire" => DAY1 * 365,
	];

	const TRY_AUTHENTICATION_TOKEN = [
		"key"    => "TRY_AUTHENTICATION_TOKEN",
		"limit"  => 500,
		"expire" => HOUR1,
	];

	const AUTH_MAIL_ENTERING_PASSWORD = [
		"key"    => "AUTH_MAIL_ENTERING_PASSWORD",
		"limit"  => 7,
		"expire" => 20 * 60,
	];

	const AUTH_SSO = [
		"key"    => "AUTH_SSO",
		"limit"  => 7,
		"expire" => 15 * 60,
	];

	const AUTH_LDAP = [
		"key"    => "AUTH_LDAP",
		"limit"  => 7,
		"expire" => 15 * 60,
	];

	# endregion
	##########################################################

	##########################################################
	# region security/mail
	##########################################################

	const MAIL_CHANGE_PASSWORD = [
		"key"    => "MAIL_CHANGE_PASSWORD",
		"limit"  => 7,
		"expire" => 20 * 60, // 20 минут
	];

	const MAIL_TRY_RESET_PASSWORD = [
		"key"    => "MAIL_TRY_RESET_PASSWORD",
		"limit"  => 7,
		"expire" => 20 * 60, // 20 минут
	];

	const MAIL_FINISH_RESET_PASSWORD = [
		"key"    => "MAIL_FINISH_RESET_PASSWORD",
		"limit"  => 7,
		"expire" => 20 * 60, // 20 минут
	];

	const MAIL_ADD = [
		"key"    => "MAIL_ADD",
		"limit"  => 3,
		"expire" => DAY1,
	];

	const MAIL_ADD_ON_SET_PASSWORD = [
		"key"    => "MAIL_ADD_ON_SET_PASSWORD",
		"limit"  => 7,
		"expire" => 20 * 60, // 20 минут
	];

	const MAIL_ADD_ON_CONFIRM_CODE = [
		"key"    => "MAIL_ADD_ON_CONFIRM_CODE",
		"limit"  => 7,
		"expire" => 20 * 60, // 20 минут
	];

	const MAIL_CHANGE = [
		"key"    => "MAIL_CHANGE",
		"limit"  => 3,
		"expire" => DAY1, // 1 день
	];

	const MAIL_CHANGE_CONFIRM = [
		"key"    => "MAIL_CHANGE_CONFIRM",
		"limit"  => 3,
		"expire" => DAY1, // 1 день
	];

	# endregion
	##########################################################

	##########################################################
	# region security/phone
	##########################################################

	const PHONE_ADD = [
		"key"    => "PHONE_ADD",
		"limit"  => 3,
		"expire" => DAY1,
	];

	const PHONE_CONFIRM_ADDITION = [
		"key"    => "PHONE_CONFIRM_ADDITION",
		"limit"  => 7,
		"expire" => 20 * 60,
	];

	# endregion
	##########################################################

	##########################################################
	# region security/device
	##########################################################

	const DEVICE_INVALIDATE = [
		"key"    => "DEVICE_INVALIDATE",
		"limit"  => 100,
		"expire" => 20 * 60, // 20 минут
	];

	const DEVICE_INVALIDATE_OTHER = [
		"key"    => "DEVICE_INVALIDATE_OTHER",
		"limit"  => 20,
		"expire" => DAY1,
	];

	# endregion
	##########################################################

	protected const _DB_KEY    = "pivot_system";
	protected const _TABLE_KEY = "antispam_user";

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	/**
	 * получить лимит ключа блокировки
	 *
	 * @return int
	 */
	public static function getBlockKeyLimit(array $block_key):int {

		return $block_key["limit"];
	}

	// проверяем на срабатывание блокировок по конкретному ключу
	// пишем статистику по срабатыванию блокировки если необходимо
	public static function throwIfBlocked(int $user_id, array $block_key, bool $is_custom_error = false):int {

		if (self::needCheckIsBlocked()) {
			return self::getBlockKeyLimit($block_key);
		}

		// получаем текущее состояние блокировки
		$row = self::_getRow($user_id, $block_key);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {

			// если нужна кастомная ошибка
			if ($is_custom_error) {
				throw new cs_blockException($row["expires_at"]);
			}

			throw new BlockException("User with user_id [{$user_id}] blocked with key: '{$block_key["key"]}'", $row["expires_at"]);
		}

		// обновляем запись
		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expires_at"]);

		return $row["count"] + 1;
	}

	/**
	 * узнать сработала ли блокировка без инкремента
	 *
	 * @param int   $user_id
	 * @param array $block_key
	 *
	 * @return int
	 * @throws BlockException
	 */
	public static function check(int $user_id, array $block_key):int {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($user_id, $block_key);

		// если время действие активно и превысили лимит - блокировка сработала
		if (self::_isBlock($row, $block_key["limit"])) {

			throw new BlockException("User with user_id [{$user_id}] blocked with key: '{$block_key["key"]}'", $row["expires_at"]);
		}

		return $row["count"];
	}

	/**
	 * уменьшаем блокировку
	 *
	 */
	public static function decrement(int $user_id, array $block_key, int $decrement_value = 1):void {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($user_id, $block_key);

		$count = $row["count"] - $decrement_value;
		if ($count < 0) {
			$count = 0;
		}

		// обновляем запись
		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $count, $row["expires_at"]);
	}

	/**
	 * увеличиваем блокировку
	 *
	 */
	public static function increment(int $user_id, array $block_key):void {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($user_id, $block_key);

		// обновляем запись
		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expires_at"]);
	}

	/**
	 * получить запись блокировки
	 *
	 */
	protected static function _getWithNullifiedCountIfNeeded(int $user_id, array $block_key):array {

		$row = self::_get($user_id, $block_key["key"]);

		// если время превысило expires_at, то сбрасываем блокировку
		if (time() > $row["expires_at"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expires_at"]   = time() + $block_key["expire"];
		}

		return $row;
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
	public static function assertNotBlock(int $user_id, array $block_key):bool {

		// получаем текущее состояние блокировки
		$row = self::_get($user_id, $block_key["key"]);

		// если время превысило expires_at, то блокировка неактуальна и не сработает
		if (time() > $row["expires_at"]) {
			return false;
		}

		// если превысили лимит - блокировка сработала, возвращаем true
		if ($row["count"] >= $block_key["limit"]) {

			throw new BlockException("User with user_id [{$user_id}] blocked with key: '{$block_key["key"]}'", $row["expires_at"]);
		}

		// во всех иных случаях - false
		return false;
	}

	// получаем информацию о блокировке
	public static function get(int $user_id, array $block_key):array {

		return self::_get($user_id, $block_key["key"]);
	}

	/**
	 * Очистка всех блокировок, если передан user_id - чистим только по пользователю
	 *
	 * @throws \parseException
	 */
	public static function clearAll(int $user_id = 0):void {

		// проверяем выполняется ли код на тестовом сервере
		assertTestServer();

		// формируем стандартный запрос для удаления всех блокировок
		$query = "DELETE FROM `?p` WHERE ?i = ?i LIMIT ?i";
		$args  = [1, 1, 1000000];

		// если передан пользователь - формируем запрос на удаление
		if ($user_id > 0) {

			$query = "DELETE FROM `?p` WHERE user_id = ?i LIMIT ?i";
			$args  = [$user_id, 1000000];
		}

		// индекс не требуется
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, ...$args);
	}

	/**
	 * Получить кол-во по названию блокировки
	 *
	 */
	public static function getCountByKey(string $key, int $user_id):int {

		return self::_get($user_id, $key)["count"];
	}

	/**
	 * устанавливаем значение блокировки по названию блокировки
	 *
	 */
	public static function setByKey(string $key, int $user_id, int $count, int $expires_at):void {

		self::_set($user_id, $key, 0, $count, $expires_at);
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
			->getOne("SELECT * FROM `?p` WHERE `user_id`=?s AND `key`=?s LIMIT ?i", self::_TABLE_KEY, $user_id, $key, 1);

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

	/**
	 * проверяем сработала ли блокировка
	 *
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
}
