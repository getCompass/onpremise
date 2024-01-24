<?php

namespace Compass\Announcement;

/**
 * класс для блокировки пользователя по IP адресу
 */
class Type_Antispam_Ip {

	protected const _DB_KEY    = "announcement_service";
	protected const _TABLE_KEY = "antispam_ip";

	/** @var array неверный токен доступа */
	const WRONG_INITIAL_TOKEN = [
		"key"    => "WRONG_INITIAL_TOKEN",
		"limit"  => 20,
		"expire" => HOUR1,
	];

	/** @var array неверный токен подключения */
	const WRONG_AUTHORIZATION_TOKEN = [
		"key"    => "WRONG_AUTHORIZATION_TOKEN",
		"limit"  => 20,
		"expire" => HOUR1,
	];

	/**
	 * узнать сработала ли блокировка без инкремента
	 *
	 * @param array $block_key
	 *
	 * @return bool
	 * @throws cs_blockException
	 */
	public static function check(array $block_key):bool {

		$ip_address = getIp();

		// получаем текущее состояние блокировки
		$row = self::_get($ip_address, $block_key["key"]);

		// если время превысило expire, то блокировка неактуальна и не сработает
		if (time() > $row["expire"]) {
			return false;
		}

		// если превысили лимит - блокировка сработала, возвращаем true
		if ($row["count"] >= $block_key["limit"]) {
			throw new cs_blockException($row["expire"], "User with ip [{$ip_address}] blocked with key: '{$block_key["key"]}'");
		}

		// во всех иных случаях - false
		return false;
	}

	/**
	 * проверяем на срабатывание блокировок по конкретному ключу
	 * пишем статистику по срабатыванию блокировки если необходимо
	 *
	 * @param array $block_key
	 *
	 * @throws cs_blockException
	 */
	public static function checkAndIncrementBlock(array $block_key):void {

		// получаем текущее состояние блокировки
		$ip_address = getIp();
		$row        = self::_getWithNullifiedCountIfNeeded($ip_address, $block_key);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {

			throw new cs_blockException($row["expire"], "User with ip [{$ip_address}] blocked with key: '{$block_key["key"]}'");
		}

		// обновляем запись
		self::_set($row["ip_address"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expire"]);
	}

	/**
	 * Уменьшаем блокировку
	 *
	 * @param array $block_key
	 */
	public static function decrement(array $block_key):void {

		$ip_address = getIp();

		$row = self::_getWithNullifiedCountIfNeeded($ip_address, $block_key);

		if ($row["count"] != 0) {

			self::_set($row["ip_address"], $row["key"], $row["is_stat_sent"], $row["count"] - 1, $row["expire"]);
		}
	}

	/**
	 * получаем информацию о блокировке
	 *
	 * @param string $ip_address
	 * @param array  $block_key
	 *
	 * @return array
	 */
	public static function get(array $block_key, string $ip_address = ""):array {

		if ($ip_address == "") {
			$ip_address = getIp();
		}

		return self::_get($ip_address, $block_key["key"]);
	}

	/**
	 * устанавливаем значение блокировки по названию блокировки
	 *
	 * @param string $key
	 * @param string $ip_address
	 * @param int    $count
	 * @param int    $expire
	 */
	public static function setByKey(string $key, string $ip_address, int $count, int $expire):void {

		self::_set($ip_address, $key, 0, $count, $expire);
	}

	/**
	 * получаем значение блокировки
	 *
	 * @param array  $block_key
	 * @param string $ip_address
	 *
	 * @return int
	 */
	public static function getCount(array $block_key, string $ip_address = ""):int {

		if ($ip_address == "") {
			$ip_address = getIp();
		}

		$row = self::_getWithNullifiedCountIfNeeded($ip_address, $block_key);
		return $row["count"];
	}

	/**
	 * Получить кол-во по названию блокировки
	 *
	 * @param string $key
	 * @param string $ip_address
	 *
	 * @return int
	 */
	public static function getCountByKey(string $key, string $ip_address):int {

		return self::_get($ip_address, $key)["count"];
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

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	/**
	 * Получаем значение (обнуляем, если истекло время)
	 *
	 * @param string $ip_address
	 * @param array  $block_key
	 *
	 * @return array
	 */
	protected static function _getWithNullifiedCountIfNeeded(string $ip_address, array $block_key):array {

		// получаем запись с блокировкой из базы
		$row = self::_get($ip_address, $block_key["key"]);

		// если время превысило expire, то сбрасываем блокировку
		if (time() > $row["expire"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expire"]       = time() + $block_key["expire"];
		}

		return $row;
	}

	/**
	 * создаем новую или обновляем существующую запись в базе
	 *
	 * @param string $ip_address
	 * @param string $key
	 * @param int    $is_stat_sent
	 * @param int    $count
	 * @param int    $expire
	 */
	protected static function _set(string $ip_address, string $key, int $is_stat_sent, int $count, int $expire):void {

		$set = [
			"ip_address"   => $ip_address,
			"key"          => $key,
			"is_stat_sent" => $is_stat_sent,
			"count"        => $count,
			"expire"       => $expire,
		];

		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, $set);
	}

	// пытаемся получить информацию по ключу и айпи-адресу
	protected static function _get(string $ip_address, string $key):array {

		$row = ShardingGateway::database(self::_DB_KEY)
			->getOne("SELECT * FROM `?p` WHERE `ip_address`=?s AND `key`=?s LIMIT ?i", self::_TABLE_KEY, $ip_address, $key, 1);

		// если записи нет - формируем
		if (!isset($row["ip_address"])) {

			$row = [
				"ip_address"   => $ip_address,
				"key"          => $key,
				"is_stat_sent" => 0,
				"count"        => 0,
				"expire"       => 0,
			];
		}

		return $row;
	}
}