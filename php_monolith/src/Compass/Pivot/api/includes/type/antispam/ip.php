<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;

/**
 * класс для блокировки пользователя по IP адресу
 */
class Type_Antispam_Ip {

	protected const _DB_KEY    = "pivot_system";
	protected const _TABLE_KEY = "antispam_ip";

	const WRONG_SIGN_IN_CODE = [
		"key"    => "WRONG_SIGN_IN_CODE",
		"limit"  => 2,
		"expire" => HOUR1,
	];

	const INCORRECT_INVITELINK = [
		"key"    => "INCORRECT_INVITELINK",
		"limit"  => 2,
		"expire" => HOUR1,
	];

	const DETACH_VOIP_TOKEN = [
		"key"    => "DETACH_VOIP_TOKEN",
		"limit"  => 20,
		"expire" => 60,
	];

	const BEGIN_INCORRECT_PHONE_NUMBER = [
		"key"    => "BEGIN_INCORRECT_PHONE_NUMBER",
		"limit"  => 7,
		"expire" => 60 * 15,
	];

	const JOIN_LINK_VALIDATE = [
		"key"    => "JOIN_LINK_VALIDATE",
		"limit"  => 10,
		"expire" => HOUR2,
	];

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	/**
	 * проверяем на срабатывание блокировок по конкретному ключу
	 * пишем статистику по срабатыванию блокировки если необходимо
	 *
	 * @throws \blockException
	 */
	public static function checkAndIncrementBlock(array $block_key):void {

		// получаем текущее состояние блокировки
		$ip_address = getIp();
		$row        = self::_getWithNullifiedCountIfNeeded($ip_address, $block_key);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {

			throw new cs_blockException($row["expires_at"], "User with ip [{$ip_address}] blocked with key: '{$block_key["key"]}'");
		}

		// обновляем запись
		self::_set($row["ip_address"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expires_at"]);
	}

	/**
	 * Проверяем блокировку, если нет, то увеличиваем, если есть, проверяем рекапчу
	 *
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	public static function incrementAndAssertRecaptchaIfBlocked(array $block_key, string|false $grecaptcha_response):void {

		if (Type_Antispam_User::needCheckIsBlocked()) {
			return;
		}

		try {
			self::checkAndIncrementBlock($block_key);
		} catch (BlockException) {
			Type_Captcha_Main::assertCaptcha($grecaptcha_response);
		}
	}

	/**
	 * узнать сработала ли блокировка без инкремента
	 *
	 * @throws cs_blockException
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

			throw new cs_blockException($row["expires_at"], "User with ip [{$ip_address}] blocked with key: '{$block_key["key"]}'");
		}

		// во всех иных случаях - false
		return false;
	}

	/**
	 * Уменьшаем блокировку
	 *
	 */
	public static function decrement(array $block_key):void {

		$ip_address = getIp();

		$row = self::_getWithNullifiedCountIfNeeded($ip_address, $block_key);

		if ($row["count"] != 0) {
			self::_set($row["ip_address"], $row["key"], $row["is_stat_sent"], $row["count"] - 1, $row["expires_at"]);
		}
	}

	/**
	 * получаем информацию о блокировке
	 *
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
	 */
	public static function setByKey(string $key, string $ip_address, int $count, int $expires_at):void {

		self::_set($ip_address, $key, 0, $count, $expires_at);
	}

	/**
	 * получаем значение блокировки
	 *
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
	protected static function _getWithNullifiedCountIfNeeded(string $ip_address, array $block_key):array {

		// получаем запись с блокировкой из базы
		$row = self::_get($ip_address, $block_key["key"]);

		// если время превысило expires_at, то сбрасываем блокировку
		if (time() > $row["expires_at"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expires_at"]   = time() + $block_key["expire"];
		}

		return $row;
	}

	/**
	 * создаем новую или обновляем существующую запись в базе
	 *
	 */
	protected static function _set(string $ip_address, string $key, int $is_stat_sent, int $count, int $expires_at):void {

		$set = [
			"ip_address"   => $ip_address,
			"key"          => $key,
			"is_stat_sent" => $is_stat_sent,
			"count"        => $count,
			"expires_at"   => $expires_at,
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
				"expires_at"   => 0,
			];
		}

		return $row;
	}
}