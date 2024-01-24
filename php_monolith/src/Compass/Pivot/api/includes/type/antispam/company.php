<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;

/**
 * класс для блокировки компании
 */
class Type_Antispam_Company extends Type_Antispam_Main {

	##########################################################
	# region константы для блокировок
	##########################################################

	const USER_COMPANY_SESSION_TOKEN_LIMIT = [
		"key"    => "USER_COMPANY_SESSION_TOKEN_LIMIT",
		"limit"  => 2,
		"expire" => 5 * 60,
	];

	const WRONG_TWO_FA_TOKEN = [
		"key"    => "WRONG_TWO_FA_TOKEN",
		"limit"  => 2,
		"expire" => 5 * 60, // пять минут
	];

	# endregion
	##########################################################

	protected const _DB_KEY    = "pivot_system";
	protected const _TABLE_KEY = "antispam_company";

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	/**
	 * проверяем на срабатывание блокировок по конкретному ключу
	 * пишем статистику по срабатыванию блокировки если необходимо
	 *
	 * @throws \blockException
	 */
	public static function checkAndIncrementBlock(int $company_id, array $block_key, int $increment_value = 1):void {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($company_id, $block_key);

		// если превысили лимит - выбрасываем исключение
		if (self::_isBlock($row, $block_key["limit"])) {

			throw new BlockException("Company [{$company_id}] blocked with key: '{$block_key["key"]}'");
		}

		// обновляем запись
		self::_set($row["company_id"], $row["key"], $row["is_stat_sent"], $row["count"] + $increment_value, $row["expires_at"]);
	}

	/**
	 * узнать сработала ли блокировка без инкремента
	 *
	 * @throws \blockException
	 */
	public static function check(int $company_id, array $block_key):int {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($company_id, $block_key);

		// если время действие активно и превысили лимит - блокировка сработала
		if (self::_isBlock($row, $block_key["limit"])) {

			throw new cs_DeclinedCompanyInviteLimitExceeded("Company [{$company_id}] blocked with key: '{$block_key["key"]}'");
		}

		return $row["count"];
	}

	/**
	 * увеличиваем блокировку без проверки
	 *
	 */
	public static function increment(int $company_id, array $block_key, int $increment_value = 1):void {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($company_id, $block_key);

		// обновляем запись
		self::_set($row["company_id"], $row["key"], $row["is_stat_sent"], $row["count"] + $increment_value, $row["expires_at"]);
	}

	/**
	 * уменьшаем блокировку
	 *
	 */
	public static function decrement(int $company_id, array $block_key, int $decrement_value = 1):void {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($company_id, $block_key);

		$count = $row["count"] - $decrement_value;
		if ($count < 0) {
			$count = 0;
		}

		// обновляем запись
		self::_set($row["company_id"], $row["key"], $row["is_stat_sent"], $count, $row["expires_at"]);
	}

	/**
	 * Очистка всех блокировок
	 */
	public static function clearAll():void {

		ShardingGAteway::database(self::_DB_KEY)->delete("DELETE FROM `?p` WHERE ?i = ?i LIMIT ?i", self::_TABLE_KEY, 1, 1, 1000000);
	}

	/**
	 * Установка блокировок по ключу блокировки.
	 *
	 */
	public static function set(int $company_id, array $block_data, int $value = 0):void {

		// получаем текущее состояние блокировки
		$row = self::_getWithNullifiedCountIfNeeded($company_id, $block_data);

		$count      = max(0, $value);
		$expires_at = time() + $block_data["expire"];

		// обновляем запись
		self::_set($row["company_id"], $row["key"], $row["is_stat_sent"], $count, $expires_at);
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	/**
	 * обновляем блокировку
	 *
	 */
	protected static function _set(string $company_id, string $key, int $is_stat_sent, int $count, int $expires_at):void {

		$set = [
			"company_id"   => $company_id,
			"key"          => $key,
			"is_stat_sent" => $is_stat_sent,
			"count"        => $count,
			"expires_at"   => $expires_at,
		];

		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, $set);
	}

	// получаем информацию по ключу и компании
	protected static function _get(int $company_id, string $key):array {

		$row = ShardingGateway::database(self::_DB_KEY)
			->getOne("SELECT * FROM `?p` WHERE company_id=?i AND `key`=?s LIMIT ?i", self::_TABLE_KEY, $company_id, $key, 1);

		// если записи нет - формируем
		if (!isset($row["company_id"])) {

			$row = [
				"company_id"   => $company_id,
				"key"          => $key,
				"is_stat_sent" => 0,
				"count"        => 0,
				"expires_at"   => 0,
			];
		}

		return $row;
	}

	/**
	 * получить запись блокировки
	 *
	 */
	protected static function _getWithNullifiedCountIfNeeded(int $company_id, array $block_key):array {

		$row = self::_get($company_id, $block_key["key"]);

		// если время превысило expires_at, то сбрасываем блокировку
		if (time() > $row["expires_at"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expires_at"]   = time() + $block_key["expire"];
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