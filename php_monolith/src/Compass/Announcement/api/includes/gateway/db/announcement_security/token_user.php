<?php

namespace Compass\Announcement;

/**
 * Класс-интерфейс для таблицы announcement
 */
class Gateway_Db_AnnouncementSecurity_TokenUser extends Gateway_Db_AnnouncementSecurity_Main {

	protected const _TABLE_KEY = "token_user";

	/**
	 * Вставка записи
	 *
	 * @param string $token
	 * @param int    $user_id
	 * @param string $bound_session_key
	 * @param int    $expires_at
	 *
	 * @return Struct_Db_AnnouncementSecurity_TokenUser
	 * @throws \queryException
	 */
	public static function insert(string $token, int $user_id, string $bound_session_key, int $expires_at):Struct_Db_AnnouncementSecurity_TokenUser {

		// получаем ключ базы данных
		$shard_key = self::_getDataBaseKey();

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		$insert_row = [
			"token"             => $token,
			"user_id"           => $user_id,
			"bound_session_key" => $bound_session_key,
			"created_at"        => time(),
			"updated_at"        => time(),
			"expires_at"        => $expires_at,
		];

		// осуществляем запрос
		ShardingGateway::database($shard_key)->insert($table_name, $insert_row);

		return self::_makeStructFromRow($insert_row);
	}
	/**
	 * Вставка или обновление записи
	 *
	 * @param string $token
	 * @param int    $user_id
	 * @param string $bound_session_key
	 * @param int    $expires_at
	 *
	 * @return Struct_Db_AnnouncementSecurity_TokenUser
	 * @throws \queryException
	 */
	public static function insertOrUpdate(string $token, int $user_id, string $bound_session_key, int $expires_at):Struct_Db_AnnouncementSecurity_TokenUser {

		// получаем ключ базы данных
		$shard_key = self::_getDataBaseKey();

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		$insert_row = [
			"token"             => $token,
			"user_id"           => $user_id,
			"bound_session_key" => $bound_session_key,
			"created_at"        => time(),
			"updated_at"        => time(),
			"expires_at"        => $expires_at,
		];

		$set = [
			"token"             => $token,
			"updated_at"        => time(),
			"expires_at"        => $expires_at,
		];

		// осуществляем запрос
		ShardingGateway::database($shard_key)->insertOrUpdate($table_name, $insert_row, $set);

		return self::_makeStructFromRow($insert_row);
	}

	/**
	 * Получения записи
	 *
	 * @param int    $user_id
	 * @param string $bound_session_key
	 *
	 * @return Struct_Db_AnnouncementSecurity_TokenUser
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $user_id, string $bound_session_key):Struct_Db_AnnouncementSecurity_TokenUser {

		// получаем ключ базы данных
		$shard_key = self::_getDataBaseKey();

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		// EXPLAIN KEY PRIMARY
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `bound_session_key` = ?s LIMIT ?i";

		$row = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $bound_session_key, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_makeStructFromRow($row);
	}

	/**
	 * Получение истёкших токенов
	 *
	 * @param string $table_shard_name
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return array
	 */
	public static function getExpiredTokenList(string $table_shard_name, int $limit = 20, int $offset = 0):array {

		// получаем ключ базы данных
		$shard_key = self::_getDataBaseKey();

		// EXPLAIN KEY get_expired
		$query = "SELECT * FROM `?p` WHERE `expires_at` < ?i LIMIT ?i OFFSET ?i";

		return ShardingGateway::database($shard_key)->getAll($query, $table_shard_name, time(), $limit, $offset);
	}

	/**
	 * Получение количества записей
	 *
	 * @param string $table_shard_name
	 *
	 * @return int
	 */
	public static function getTotalCount(string $table_shard_name):int {

		// запрос проверен на EXPLAIN (INDEX=get_expired)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDataBaseKey())->getOne($query, $table_shard_name, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param string $table_shard_name
	 * @param int    $expires_at
	 *
	 * @return int
	 */
	public static function getExpiredCount(string $table_shard_name, int $expires_at):int {

		// запрос проверен на EXPLAIN (INDEX=get_expired)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `expires_at` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDataBaseKey())->getOne($query, $table_shard_name, $expires_at, 1);
		return $row["count"];
	}

	/**
	 * Удаление
	 *
	 * @param int    $user_id
	 * @param string $bound_session_key
	 */
	public static function delete(int $user_id, string $bound_session_key):void {

		// получаем ключ базы данных
		$shard_key = self::_getDataBaseKey();

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		// EXPLAIN KEY PRIMARY
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `bound_session_key` = ?s LIMIT ?i";

		ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, $bound_session_key, 1);
	}

	/**
	 * Удаляет все токены для указанного пользователя.
	 *
	 * @param int $user_id
	 */
	public static function deleteAllUserTokens(int $user_id):void {

		$shard_key  = self::_getDataBaseKey();
		$table_name = self::_getTableName($user_id);

		// сгруппированные токены
		$bound_session_key_list = [];

		// параметры выборки
		$limit  = 100;
		$offset = 0;

		// EXPLAIN KEY PRIMARY
		$select_query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i OFFSET ?i";

		do {

			// получаем все токены по N штук
			$result = ShardingGateway::database($shard_key)->getAll($select_query, $table_name, $user_id, $limit, $offset);

			// формируем список с сессиями и удаляем
			$bound_session_key_list = $bound_session_key_list + array_column($result, "bound_session_key");
			$offset                 = $offset + $limit;
		} while (count($result) > 0);

		// EXPLAIN KEY PRIMARY
		$delete_query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `bound_session_key` IN (?a) LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($delete_query, $table_name, $user_id, $bound_session_key_list, count($bound_session_key_list));
	}

	/**
	 * Удаляем устаревшие записи
	 *
	 * @param string $table_shard_name
	 * @param int    $expires_at
	 * @param int    $limit
	 *
	 * @return int
	 */
	public static function deleteExpired(string $table_shard_name, int $expires_at, int $limit):int {

		// запрос проверен на EXPLAIN (INDEX=get_expired)
		$query = "DELETE FROM `?p` WHERE `expires_at` < ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDataBaseKey())->delete($query, $table_shard_name, $expires_at, $limit);
	}

	/**
	 * Выполняем оптимизацию таблиц.
	 */
	public static function optimize():void {

		// доступно только из консоли
		// или для крона на серверах разработки
		if (!(isCLi() || (isCron() && isTestServer()))) {
			return;
		}

		$shard_key = self::_getDataBaseKey();

		foreach (static::getTableShards() as $table_key) {

			// EXPLAIN не требуется
			$query = "OPTIMIZE TABLE `{$shard_key}`.`{$table_key}`;";
			ShardingGateway::database($shard_key)->query($query);
		}
	}

	/**
	 * Метод возвращает все возможные шарды таблицы.
	 *
	 * @return array
	 */
	public static function getTableShards():array {

		$output = [];

		for ($i = 0; $i < 10; $i++) {
			$output[] = self::_TABLE_KEY . "_" . $i;
		}

		return $output;
	}

	# region protected

	/**
	 * Создание структуру из строки бд
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_AnnouncementSecurity_TokenUser
	 */
	#[\JetBrains\PhpStorm\Pure]
	protected static function _makeStructFromRow(array $row):Struct_Db_AnnouncementSecurity_TokenUser {

		return new Struct_Db_AnnouncementSecurity_TokenUser(
			$row["token"],
			$row["user_id"],
			$row["bound_session_key"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"]
		);
	}

	/**
	 * метод возвращает название таблицы
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	protected static function _getTableName(int $user_id):string {

		return self::_TABLE_KEY . "_" . $user_id % 10;
	}

	# endregion protected
}