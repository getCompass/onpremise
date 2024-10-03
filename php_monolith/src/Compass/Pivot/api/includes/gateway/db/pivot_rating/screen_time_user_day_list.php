<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_rating_{10m}.screen_time_user_day_list_{1}
 */
class Gateway_Db_PivotRating_ScreenTimeUserDayList extends Gateway_Db_PivotRating_Main {

	protected const _TABLE_KEY = "screen_time_user_day_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания записи
	 */
	public static function insert(int $user_id, string $user_local_date, array $screen_time_list):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = [
			"user_id"          => $user_id,
			"user_local_date"  => $user_local_date,
			"created_at"       => time(),
			"updated_at"       => 0,
			"screen_time_list" => $screen_time_list,
		];
		ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * Метод для получения записи
	 *
	 * @return Struct_Db_PivotRating_ScreenTimeUserDay[]
	 */
	public static function getByUserIdAndUserLocalDateList(int $user_id, array $user_local_date_list):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `user_local_date` IN (?a) LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $user_local_date_list, count($user_local_date_list));

		return self::_listToStruct($row);
	}

	/**
	 * Получаем запись
	 *
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $user_id, string $user_local_date):Struct_Db_PivotRating_ScreenTimeUserDay {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `user_local_date` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $user_local_date, 1);

		return self::_rowToStruct($row);
	}

	/**
	 * Получаем запись на обновление
	 *
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getForUpdate(int $user_id, string $user_local_date):Struct_Db_PivotRating_ScreenTimeUserDay {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `user_local_date` = ?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $user_local_date, 1);

		return self::_rowToStruct($row);
	}

	/**
	 * Обновляем запись
	 */
	public static function update(int $user_id, string $user_local_date, array $set):void {

		if (!isset($set["updated_at"])) {
			$set["updated_at"] = time();
		}

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `user_local_date` = ?s LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $user_local_date, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем таблицу
	 */
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}

	/**
	 * Форматируем список записей из базы
	 * @return Struct_Db_PivotRating_ScreenTimeUserDay[]
	 */
	protected static function _listToStruct(array $list):array {

		return array_map(fn(array $row) => self::_rowToStruct($row), $list);
	}

	/**
	 * Форматируем запись в структуру
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotRating_ScreenTimeUserDay {

		if (!isset($row["user_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("not found row");
		}

		return Struct_Db_PivotRating_ScreenTimeUserDay::fromRow($row);
	}
}