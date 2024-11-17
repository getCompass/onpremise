<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.user_list_{1}
 */
class Gateway_Db_PivotUser_UserList extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "user_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotUser_User $user):string {

		$shard_key  = self::_getDbKey($user->user_id);
		$table_name = self::_getTableKey($user->user_id);

		$insert = [
			"user_id"               => $user->user_id,
			"npc_type"              => $user->npc_type,
			"invited_by_partner_id" => $user->invited_by_partner_id,
			"created_at"            => $user->created_at,
			"updated_at"            => $user->updated_at,
			"country_code"          => $user->country_code,
			"full_name"             => $user->full_name,
			"avatar_file_map"       => $user->avatar_file_map,
			"extra"                 => $user->extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $user_id, array $set):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotUser_User::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id):Struct_Db_PivotUser_User {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i";

		$row = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * метод для получения и блокировки записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $user_id):Struct_Db_PivotUser_User {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i FOR UPDATE";

		$row = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * Метод получения списка пользователей
	 *
	 */
	public static function getList(int $user_id, array $user_list_id):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` IN (?a) LIMIT ?i";

		$rows = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_list_id, count($user_list_id));

		if (count($rows) === 0) {
			return [];
		}

		$result_rows = [];

		foreach ($rows as $row) {
			$result_rows[] = self::_rowToStruct($row);
		}

		return $result_rows;
	}

	/**
	 * Получаем количество пользователей в приложении
	 *
	 * @return int Количество пользователей в приложении
	 */
	public static function getUserCount():int {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// формируем и осуществляем запрос
		$query  = "SELECT COUNT(*) AS `count` FROM `?p` WHERE `user_id` > (?i) LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, 0, 1);

		return (int) $result["count"];
	}

	/**
	 * Получаем количество регистраций за промежуток времени
	 *
	 */
	public static function getCountByInterval(int $from_date, int $to_date):int {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN (INDEX=`created_at`)
		$query  = "SELECT COUNT(*) AS `count` FROM `?p` WHERE `created_at` BETWEEN ?i AND ?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $from_date, $to_date, 1);

		return (int) $result["count"];
	}

	/**
	 * Получаем список зарегистрированных пользователей за промежуток времени
	 *
	 * @return Struct_Db_PivotUser_User[]
	 */
	public static function getAllByInterval(int $from_date, int $to_date):array {

		// получаем количество пользователей
		$count_of_users = self::getCountByInterval($from_date, $to_date);

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN (INDEX=`created_at`)
		$query = "SELECT * FROM `?p` WHERE `created_at` BETWEEN ?i AND ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $from_date, $to_date, $count_of_users);

		// форматируем перед отдачей
		$result_rows = [];
		foreach ($list as $row) {
			$result_rows[] = self::_rowToStruct($row);
		}

		return $result_rows;
	}

	/**
	 * Получаем всех пользователей
	 *
	 * @param int $count
	 * @param int $offset
	 *
	 * @return Struct_Db_PivotUser_User[]
	 */
	public static function getAll(int $count, int $offset):array {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE ?i = ?i LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database($shard_key)->getAll($query, $table_name, 1, 1, $count, $offset);

		if (count($rows) === 0) {
			return [];
		}

		$result_rows = [];

		foreach ($rows as $row) {
			$result_rows[] = self::_rowToStruct($row);
		}

		return $result_rows;
	}

	/**
	 * Получаем пользователей по массиву user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function getByUserIdList(array $user_id_list):array {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` in (?a) LIMIT ?i";
		$rows  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id_list, count($user_id_list));

		if (count($rows) === 0) {
			return [];
		}

		$result_rows = [];

		foreach ($rows as $row) {
			$result_rows[] = self::_rowToStruct($row);
		}

		return $result_rows;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получает таблицу
	 *
	 */
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}

	/**
	 * Форматирует запись в структуру
	 *
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotUser_User {

		return new Struct_Db_PivotUser_User(
			$row["user_id"],
			$row["npc_type"],
			$row["invited_by_partner_id"],
			$row["invited_by_user_id"],
			$row["last_active_day_start_at"],
			$row["created_at"],
			$row["updated_at"],
			$row["full_name_updated_at"],
			$row["country_code"],
			$row["full_name"],
			$row["avatar_file_map"],
			fromJson($row["extra"])
		);
	}
}