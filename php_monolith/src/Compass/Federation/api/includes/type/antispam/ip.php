<?php

namespace Compass\Federation;

use BaseFrame\Exception\Request\BlockException;

/**
 * класс для блокировки пользователя по IP адресу
 */
class Type_Antispam_Ip {

	protected const _DB_KEY    = "federation_system";
	protected const _TABLE_KEY = "antispam_ip";

	const LDAP_FAILED_TRY_AUTHENTICATE = [
		"key"    => "LDAP_FAILED_TRY_AUTHENTICATE",
		"limit"  => 7,
		"expire" => 15 * 60,
	];

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	/**
	 * переопределяем лимит блокировки
	 *
	 * @return array
	 */
	public static function overrideBlockKeyLimit(array $block_key, int $limit):array {

		$block_key["limit"] = $limit;
		return $block_key;
	}

	/**
	 * проверяем на срабатывание блокировок по конкретному ключу
	 * пишем статистику по срабатыванию блокировки если необходимо
	 *
	 * @throws BlockException
	 */
	public static function checkAndIncrementBlock(array $block_key):int {

		// получаем текущее состояние блокировки
		$ip_address = getIp();
		$row        = self::_getWithNullifiedCountIfNeeded($ip_address, $block_key);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {

			throw new BlockException("User with ip [{$ip_address}] blocked with key: '{$block_key["key"]}'", $row["expires_at"]);
		}

		// обновляем запись
		self::_set($row["ip_address"], $row["key"], $row["count"] + 1, $row["expires_at"]);

		return $row["count"] + 1;
	}

	/**
	 * узнать сработала ли блокировка без инкремента
	 *
	 * @throws BlockException
	 */
	public static function check(array $block_key):bool {

		$ip_address = getIp();

		// получаем текущее состояние блокировки
		$row = self::_get($ip_address, $block_key["key"]);

		// если время превысило expires_at, то блокировка неактуальна и не сработает
		if (time() > $row["expires_at"]) {
			return false;
		}

		// если превысили лимит - блокировка сработала, возвращаем true
		if ($row["count"] >= $block_key["limit"]) {

			throw new BlockException("User with ip [{$ip_address}] blocked with key: '{$block_key["key"]}'", $row["expires_at"]);
		}

		// во всех иных случаях - false
		return false;
	}

	/**
	 * Уменьшаем блокировку
	 */
	public static function decrement(array $block_key):void {

		$ip_address = getIp();

		$row = self::_getWithNullifiedCountIfNeeded($ip_address, $block_key);

		if ($row["count"] != 0) {
			self::_set($row["ip_address"], $row["key"], $row["count"] - 1, $row["expires_at"]);
		}
	}

	/**
	 * получаем информацию о блокировке
	 */
	public static function get(array $block_key, string $ip_address = ""):array {

		if ($ip_address == "") {
			$ip_address = getIp();
		}

		return self::_get($ip_address, $block_key["key"]);
	}

	/**
	 * устанавливаем значение блокировки по названию блокировки
	 */
	public static function setByKey(string $key, string $ip_address, int $count, int $expires_at):void {

		self::_set($ip_address, $key, $count, $expires_at);
	}

	/**
	 * получаем значение блокировки
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
	 */
	protected static function _getWithNullifiedCountIfNeeded(string $ip_address, array $block_key):array {

		// получаем запись с блокировкой из базы
		$row = self::_get($ip_address, $block_key["key"]);

		// если время превысило expires_at, то сбрасываем блокировку
		if (time() > $row["expires_at"]) {

			$row["count"]      = 0;
			$row["expires_at"] = time() + $block_key["expire"];
		}

		return $row;
	}

	/**
	 * создаем новую или обновляем существующую запись в базе
	 */
	protected static function _set(string $ip_address, string $key, int $count, int $expires_at):void {

		$set = [
			"ip_address" => $ip_address,
			"key"        => $key,
			"count"      => $count,
			"expires_at" => $expires_at,
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
				"ip_address" => $ip_address,
				"key"        => $key,
				"count"      => 0,
				"expires_at" => 0,
			];
		}

		return $row;
	}
}