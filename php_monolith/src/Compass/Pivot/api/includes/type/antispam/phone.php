<?php

namespace Compass\Pivot;

/**
 * класс для блокировки пользователя по phone_number_hash
 */
class Type_Antispam_Phone {

	##########################################################
	# region константы для блокировок
	##########################################################

	const AUTH = [
		"key"    => "SMS_LIMIT",
		"limit"  => 3,
		"expire" => 60 * 60,
	];

	# endregion
	##########################################################

	protected const _DB_KEY    = "pivot_system";
	protected const _TABLE_KEY = "antispam_phone";

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	/**
	 * проверяем на срабатывание блокировок по конкретному ключу
	 * пишем статистику по срабатыванию блокировки если необходимо
	 *
	 * @throws cs_AuthIsBlocked
	 */
	public static function checkAndIncrementBlock(string $phone_number_hash, array $block_key):void {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($phone_number_hash, $block_key);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {

			throw new cs_AuthIsBlocked(
				$row["expires_at"],
				"Phone [{$phone_number_hash}] blocked with key: '{$block_key["key"]}'"
			);
		}

		// обновляем запись
		self::_set($row["phone_number_hash"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expires_at"]);
	}

	/**
	 * узнать сработала ли блокировка без инкремента
	 *
	 * @throws cs_AuthIsBlocked
	 */
	public static function check(string $phone_number_hash, array $block_key):array {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($phone_number_hash, $block_key);

		// если превысили лимит - блокировка сработала, возвращаем ошибку
		if ($row["count"] >= $block_key["limit"]) {

			throw new cs_AuthIsBlocked(
				$row["expires_at"],
				"Phone [{$phone_number_hash}] blocked with key: '{$block_key["key"]}'"
			);
		}

		return $row;
	}

	/**
	 * Уменьшаем блокировку
	 *
	 */
	public static function decrement(string $phone_number_hash, array $block_key):void {

		$row = self::_getWithNullifiedCountIfNeeded($phone_number_hash, $block_key);

		if ($row["count"] != 0) {
			self::_set($row["phone_number_hash"], $row["key"], $row["is_stat_sent"], $row["count"] - 1, $row["expires_at"]);
		}
	}

	/**
	 * увеличиваем блокировку
	 *
	 */
	public static function incrementBlockRow(array $row):void {

		// обновляем запись
		self::_set($row["phone_number_hash"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expires_at"]);
	}

	/**
	 * получаем информацию о блокировке
	 *
	 */
	public static function get(string $phone_number_hash, array $block_key):array {

		return self::_get($phone_number_hash, $block_key["key"]);
	}

	/**
	 * устанавливаем значение блокировки по названию блокировки
	 *
	 */
	public static function setByKey(string $key, string $phone_number_hash, int $count, int $expires_at):void {

		self::_set($phone_number_hash, $key, 0, $count, $expires_at);
	}

	/**
	 * получаем значение блокировки
	 *
	 */
	public static function getCount(string $phone_number_hash, array $block_key):int {

		$row = self::_getWithNullifiedCountIfNeeded($phone_number_hash, $block_key);
		return $row["count"];
	}

	/**
	 * Получить кол-во по названию блокировки
	 *
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
	 */
	protected static function _getWithNullifiedCountIfNeeded(string $phone_number_hash, array $block_key):array {

		// получаем запись с блокировкой из базы
		$row = self::_get($phone_number_hash, $block_key["key"]);

		// если время превысило expires_at, то сбрасываем блокировку
		if (time() > $row["expires_at"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expires_at"]   = time() + $block_key["expire"];
		}

		return $row;
	}

	/**
	 * обновляем блокировку
	 *
	 */
	protected static function _set(string $phone_number_hash, string $key, int $is_stat_sent, int $count, int $expires_at):void {

		$set = [
			"phone_number_hash" => $phone_number_hash,
			"key"               => $key,
			"is_stat_sent"      => $is_stat_sent,
			"count"             => $count,
			"expires_at"        => $expires_at,
		];

		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, $set);
	}

	// получаем информацию по ключу и номеру телефона
	protected static function _get(string $phone_number_hash, string $key):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY). Сааль Степан 09.03.2021
		$row = ShardingGateway::database(self::_DB_KEY)
			->getOne("SELECT * FROM `?p` WHERE `phone_number_hash`=?s AND `key`=?s LIMIT ?i", self::_TABLE_KEY, $phone_number_hash, $key, 1);

		// если записи нет - формируем
		if (!isset($row["phone_number_hash"])) {

			$row = [
				"phone_number_hash" => $phone_number_hash,
				"key"               => $key,
				"is_stat_sent"      => 0,
				"count"             => 0,
				"expires_at"        => 0,
			];
		}

		return $row;
	}
}