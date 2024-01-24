<?php

namespace Compass\Announcement;

/**
 * класс для блокировки пользователя по User
 */
class Type_Antispam_User {

	##########################################################
	# region константы для блокировок
	##########################################################

	const ANNOUNCEMENT_READ = [
		"key"    => "ANNOUNCEMENT_READ",
		"limit"  => 60,
		"expire" => 5,
	];

	# endregion
	##########################################################

	protected const _DB_KEY    = "announcement_service";
	protected const _TABLE_KEY = "antispam_user";

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	// проверяем на срабатывание блокировок по конкретному ключу
	// пишем статистику по срабатыванию блокировки если необходимо
	public static function throwIfBlocked(int $user_id, array $block_key):void {

		// получаем текущее состояние блокировки
		$row = self::_getRow($user_id, $block_key);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {
			throw new cs_blockException($row["expire"], "User with user_id [{$user_id}] blocked with key: '{$block_key["key"]}'");
		}

		// обновляем запись
		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expire"]);
	}

	/**
	 * уменьшаем блокировку
	 *
	 * @param int   $user_id
	 * @param array $block_key
	 * @param int   $decrement_value
	 */
	public static function decrement(int $user_id, array $block_key, int $decrement_value = 1):void {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($user_id, $block_key);

		$count = $row["count"] - $decrement_value;
		if ($count < 0) {
			$count = 0;
		}

		// обновляем запись
		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $count, $row["expire"]);
	}

	/**
	 * получить запись блокировки
	 *
	 * @param int   $user_id
	 * @param array $block_key
	 *
	 * @return array
	 */
	protected static function _getWithNullifiedCountIfNeeded(int $user_id, array $block_key):array {

		$row = self::_get($user_id, $block_key["key"]);

		// если время превысило expire, то сбрасываем блокировку
		if (time() > $row["expire"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expire"]       = time() + $block_key["expire"];
		}

		return $row;
	}

	// получаем состояние блокировки
	protected static function _getRow(int $user_id, array $block_key):array {

		// получаем запись с блокировкой из базы
		$row = self::_get($user_id, $block_key["key"]);

		// если время превысило expire, то сбрасываем блокировку
		if (time() > $row["expire"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expire"]       = time() + $block_key["expire"];
		}

		return $row;
	}

	// узнать сработала ли блокировка без инкремента
	public static function isBlock(int $user_id, array $block_key):bool {

		// получаем текущее состояние блокировки
		$row = self::_get($user_id, $block_key["key"]);

		// если время превысило expire, то блокировка неактуальна и не сработает
		if (time() > $row["expire"]) {
			return false;
		}

		// если превысили лимит - блокировка сработала, возвращаем true
		if ($row["count"] >= $block_key["limit"]) {
			throw new cs_blockException($row["expire"], "User with user_id [{$user_id}] blocked with key: '{$block_key["key"]}'");
		}

		// во всех иных случаях - false
		return false;
	}

	// получаем информацию о блокировке
	public static function get(int $user_id, array $block_key):array {

		return self::_get($user_id, $block_key["key"]);
	}

	/**
	 * Очистка всех блокировок
	 *
	 * @throws \parseException
	 */
	public static function clearAll():void {

		// проверяем выполняется ли код на тестовом сервере
		assertTestServer();

		ShardingGateway::database(self::_DB_KEY)->delete("DELETE FROM `?p` WHERE ?i = ?i LIMIT ?i", self::_TABLE_KEY, 1, 1, 1000000);
	}

	/**
	 * Получить кол-во по названию блокировки
	 *
	 * @param string $key
	 * @param int    $user_id
	 *
	 * @return int
	 */
	public static function getCountByKey(string $key, int $user_id):int {

		return self::_get($user_id, $key)["count"];
	}

	/**
	 * устанавливаем значение блокировки по названию блокировки
	 *
	 * @param string $key
	 * @param int    $user_id
	 * @param int    $count
	 * @param int    $expire
	 */
	public static function setByKey(string $key, int $user_id, int $count, int $expire):void {

		self::_set($user_id, $key, 0, $count, $expire);
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	// создаем новую или обновляем существующую запись в базе
	protected static function _set(int $user_id, string $key, int $is_stat_sent, int $count, int $expire):void {

		$set = [
			"user_id"      => $user_id,
			"key"          => $key,
			"is_stat_sent" => $is_stat_sent,
			"count"        => $count,
			"expire"       => $expire,
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
				"expire"       => 0,
			];
		}

		return $row;
	}
}
