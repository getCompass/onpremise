<?php

namespace Compass\Speaker;

/**
 * класс для блокировки пользователя по user_id
 */
class Type_Antispam_User {

	##########################################################
	# region константы для блокировок
	##########################################################

	// -------------------------------------------------------
	// calls
	// -------------------------------------------------------

	const CALLS_TRYINIT = [
		"key"    => "CALLS_TRYINIT",
		"limit"  => 20,
		"expire" => 60 * 1,
	];

	const CALLS_DOREPORTONCONNECTION = [
		"key"    => "CALLS_DOREPORTONCONNECTION",
		"limit"  => 10,
		"expire" => 60 * 1,
	];

	const CALLS_DOPING = [
		"key"    => "CALLS_DOPING",
		"limit"  => 65,
		"expire" => 60 * 1,
	];

	# endregion
	##########################################################

	protected const _DB_KEY    = "company_system";
	protected const _TABLE_KEY = "antispam_user";

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	// проверяем на срабатывание блокировок по конкретному ключу
	// пишем статистику по срабатыванию блокировки если необходимо
	public static function throwIfBlocked(int $user_id, array $block_key, string $namespace = null, string $row_name = null, string $key_extra = null):void {

		// проверяем, что пользователь авторизован
		self::_throwIfPassedInvalidUserId($user_id);

		// получаем текущее состояние блокировки
		$key = $block_key["key"] . $key_extra;
		$row = self::_getRow($user_id, $key, $block_key["expire"]);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {

			// если необходимо - отправляем статистику (только один раз!!)
			if ($row["is_stat_sent"] == 0 && !is_null($namespace)) {

				Gateway_Bus_Statholder::inc($namespace, $row_name);
				self::_set($row["user_id"], $row["key"], 1, $row["count"], $row["expires_at"]);
			}

			if (!DEV_SERVER && !isCLi()) {

				throw new \blockException("User_id [{$user_id}] blocked with key: {$key}");
			}
		}

		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expires_at"]);
	}

	// получаем состояние блокировки
	protected static function _getRow(int $user_id, string $key, int $expire):array {

		// получаем запись с блокировкой из базы
		$row = self::_get($user_id, $key);

		// если время превысило expires_at, то сбрасываем блокировку
		if (time() > $row["expires_at"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expires_at"]   = time() + $expire;
		}

		return $row;
	}

	// узнать сработала ли блокировка без инкремента
	public static function isBlock(int $user_id, array $block_key):bool {

		// проверяем, что пользователь авторизован
		self::_throwIfPassedInvalidUserId($user_id);

		// получаем текущее состояние блокировки
		$row = self::_get($user_id, $block_key["key"]);

		// если время превысило expires_at, то блокировка неактуальна и не сработает
		if (time() > $row["expires_at"]) {
			return false;
		}

		// если превысили лимит - блокировка сработала, возвращаем true
		if ($row["count"] >= $block_key["limit"]) {
			return true;
		}

		// во всех иных случаях - false
		return false;
	}

	// получаем информацию о блокировке пользователя
	public static function get(int $user_id, string $key):array {

		// проверяем, что пользователь авторизован
		self::_throwIfPassedInvalidUserId($user_id);

		return self::_get($user_id, $key);
	}

	/**
	 * Чистим таблицу с блокировками, если передан user_id чистим только по пользователю
	 */
	public static function clearAll(int $user_id = 0):void {

		// формируем стандартный запрос для удаления всех блокировок
		$query = "DELETE FROM `?p` WHERE ?i = ?i LIMIT ?i";
		$args  = [1, 1, 1000000];

		// если передан пользователь - формируем запрос на удаление
		if ($user_id > 0) {

			$query = "DELETE FROM `?p` WHERE user_id = ?i LIMIT ?i";
			$args  = [$user_id, 1000000];
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, ... $args);
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	// выбрасываем ошибку, если пришел некорректный user_id
	protected static function _throwIfPassedInvalidUserId(int $user_id):void {

		if ($user_id < 1) {
			throw new \parseException("Not valid user_id passed to " . __CLASS__);
		}
	}

	// обновляем запись блокировки
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

	// получаем блокировку из базы
	protected static function _get(int $user_id, string $key):array {

		$row = ShardingGateway::database(self::_DB_KEY)
			->getOne("SELECT * FROM `?p` WHERE `user_id`=?i AND `key`=?s LIMIT ?i", self::_TABLE_KEY, $user_id, $key, 1);

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
