<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_rating_{10m}.screen_time_space_day_list_{1}
 */
class Gateway_Db_PivotRating_ScreenTimeSpaceDayList extends Gateway_Db_PivotRating_Main {

	protected const _TABLE_KEY = "screen_time_space_day_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания записи
	 */
	public static function insert(int $space_id, string $user_local_date, array $screen_time_list):void {

		$shard_key  = self::_getDbKey($space_id);
		$table_name = self::_getTableKey($space_id);

		$insert = [
			"space_id"         => $space_id,
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
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $space_id, string $user_local_date):Struct_Db_PivotRating_ScreenTimeSpaceDay {

		$shard_key  = self::_getDbKey($space_id);
		$table_name = self::_getTableKey($space_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `space_id` = ?i AND `user_local_date` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $space_id, $user_local_date, 1);

		return self::_rowToStruct($row);
	}

	/**
	 * Обновляем запись
	 */
	public static function update(int $space_id, string $user_local_date, array $set):void {

		if (!isset($set["updated_at"])) {
			$set["updated_at"] = time();
		}

		$shard_key  = self::_getDbKey($space_id);
		$table_name = self::_getTableKey($space_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `space_id` = ?i AND `user_local_date` = ?s LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $space_id, $user_local_date, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем таблицу
	 */
	protected static function _getTableKey(int $space_id):string {

		return self::_TABLE_KEY . "_" . ceil($space_id / 1000000);
	}

	/**
	 * Форматируем запись в структуру
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotRating_ScreenTimeSpaceDay {

		if (!isset($row["space_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("not found row");
		}

		return Struct_Db_PivotRating_ScreenTimeSpaceDay::fromRow($row);
	}
}