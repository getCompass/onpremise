<?php

namespace Compass\FileBalancer;

/**
 * класс для блокировки пользователя по user_id
 */
class Type_Antispam_User extends Type_Antispam_Main {

	##########################################################
	# region константы для блокировок
	##########################################################

	const FILES_GETINFOFORUPLOAD = [
		"key"    => "FILES_GETINFOFORUPLOAD",
		"limit"  => 100,
		"expire" => 60 * 5,
	];

	const FILES_GETINFOCROPIMAGE = [
		"key"    => "FILES_GETINFOCROPIMAGE",
		"limit"  => 30,
		"expire" => 60 * 5,
	];

	# endregion
	##########################################################

	protected const _DB_KEY    = "system";
	protected const _TABLE_KEY = "antispam_user";

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	// проверяем на срабатывание блокировок по конкретному ключу
	// пишем статистику по срабатыванию блокировки если необходимо
	public static function throwIfBlocked(int $user_id, array $block_key, ?string $namespace = null):void {

		if (self::needCheckIsBlocked()) {
			return;
		}

		// получаем текущее состояние блокировки
		$row = self::_getRow($user_id, $block_key);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {

			// если необходимо - отправляем статистику (только один раз!!)
			if ($row["is_stat_sent"] == 0 && !is_null($namespace)) {
				self::_set($row["user_id"], $row["key"], 1, $row["count"], $row["expires_at"]);
			}

			throw new \blockException("User with id [$user_id] blocked with key: {$block_key["key"]}");
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

		// проверяем, что пользователь авторизован
		if ($user_id < 1) {
			throw new parseException("Not valid user_id passed to " . __CLASS__);
		}

		// получаем текущее состояние блокировки
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
	public static function isBlock(int $user_id, array $block_key):bool {

		// проверяем, что пользователь авторизован
		if ($user_id < 1) {
			throw new parseException("Not valid user_id passed to " . __CLASS__);
		}

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

	/**
	 * Чистим таблицу с блокировками, если передан user_id чистим только по пользователю
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
		ShardingGateway::database(self::_getDbKey())->delete($query, self::_TABLE_KEY, ...$args);
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	// функция для создания новой или обновления существующей записи в базе
	protected static function _set(int $user_id, string $key, int $is_stat_sent, int $count, int $expires_at):void {

		$set = [
			"user_id"      => $user_id,
			"key"          => $key,
			"is_stat_sent" => $is_stat_sent,
			"count"        => $count,
			"expires_at"   => $expires_at,
		];

		ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_TABLE_KEY, $set);
	}

	// функция для получения записи из базы
	protected static function _get(int $user_id, string $key):array {

		$row = ShardingGateway::database(self::_getDbKey())
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

	//
	protected static function _getDbKey():string {

		return getFileDbPrefix() . "_" . self::_DB_KEY;
	}
}