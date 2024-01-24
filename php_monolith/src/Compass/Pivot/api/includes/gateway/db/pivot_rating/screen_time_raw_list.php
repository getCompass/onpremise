<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_rating_{10m}.screen_time_raw_list_{1}
 */
class Gateway_Db_PivotRating_ScreenTimeRawList extends Gateway_Db_PivotRating_Main {

	protected const _TABLE_KEY = "screen_time_raw_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания записей
	 */
	public static function insertArray(array $insert_list):void {

		$grouped_by_shard = [];

		// группируем по шардам
		foreach ($insert_list as $insert) {

			$user_id                                                                     = $insert["user_id"];
			$grouped_by_shard[self::_getDbKey($user_id)][self::_getTableKey($user_id)][] = $insert;
		}

		// для каждого шарда базы данных
		foreach ($grouped_by_shard as $db_name => $grouped_by_table_user_id_list) {

			// для каждой таблицы базы данных
			foreach ($grouped_by_table_user_id_list as $table_name => $table_insert_list) {
				ShardingGateway::database($db_name)->insertArray($table_name, $table_insert_list);
			}
		}
	}

	/**
	 * Проверяем есть ли запись за 15-ти минутку для пользователя
	 *
	 * @param int    $user_id
	 * @param string $user_local_time
	 *
	 * @return bool
	 */
	public static function isExistByUserIdAndUserLocalTime(int $user_id, string $user_local_time):bool {

		try {
			self::getByUserIdAndUserLocalTime($user_id, $user_local_time);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			return false;
		}

		return true;
	}

	/**
	 * Получаем одну запись по user_id и user_local_time
	 *
	 * @param int    $user_id
	 * @param string $user_local_time
	 *
	 * @return Struct_Db_PivotRating_ScreenTimeRaw
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getByUserIdAndUserLocalTime(int $user_id, string $user_local_time):Struct_Db_PivotRating_ScreenTimeRaw {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=user_local_time)
		$query = "SELECT * FROM `?p` FORCE INDEX(`user_local_time`) WHERE `user_id` = ?i AND `user_local_time` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $user_local_time, 1);

		return self::_rowToStruct($row);
	}

	/**
	 * Получаем список записей по user_local_time
	 *
	 * @param int $shard_id
	 * @param int $date_start
	 * @param int $date_end
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public static function getUserIdListBetweenCreatedAt(int $shard_id, int $date_start, int $date_end, int $limit, int $offset):array {

		$shard_key  = self::_getDbKey($shard_id);
		$table_name = self::_getTableKey($shard_id);

		// запрос проверен на EXPLAIN(INDEX=created_at)
		$query = "SELECT DISTINCT(user_id) as `user_id` FROM `?p` FORCE INDEX (`created_at`) WHERE `created_at` BETWEEN ?i AND ?i LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $date_start, $date_end, $limit, $offset);

		return array_column($list, "user_id");
	}

	/**
	 * Формируем массив для вставки в таблицу
	 *
	 * @param int    $user_id
	 * @param int    $space_id
	 * @param string $user_local_time
	 * @param int    $screen_time
	 *
	 * @return array
	 */
	public static function makeInsertRow(int $user_id, int $space_id, string $user_local_time, int $screen_time):array {

		return [
			"user_id"         => $user_id,
			"space_id"        => $space_id,
			"user_local_time" => $user_local_time,
			"screen_time"     => $screen_time,
			"created_at"      => time(),
		];
	}

	/**
	 * Удаляем список записей
	 * ВНИМАНИЕ!!! только для очистки старых записей
	 */
	public static function deleteListByCreatedAt(int $shard_id, int $created_at, int $limit):int {

		if (!isCron()) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("only for cron");
		}

		$shard_key  = self::_getDbKey($shard_id);
		$table_name = self::_getTableKey($shard_id);

		// запрос проверен на EXPLAIN (INDEX=created_at)
		$query = "DELETE FROM `?p` WHERE `created_at` <= ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->delete($query, $table_name, $created_at, $limit);
	}

	/**
	 * Оптимизируем таблицу
	 * ВНИМАНИЕ!!! только после очистки старых записей
	 */
	public static function optimize(int $shard_id):void {

		if (!isCron()) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("only for cron");
		}

		$shard_key  = self::_getDbKey($shard_id);
		$table_name = self::_getTableKey($shard_id);

		$query = "OPTIMIZE TABLE `{$shard_key}`.`{$table_name}`;";
		ShardingGateway::database($shard_key)->query($query);
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
	 * Форматируем запись в структуру
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotRating_ScreenTimeRaw {

		if (!isset($row["user_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("not found row");
		}

		return Struct_Db_PivotRating_ScreenTimeRaw::fromRow($row);
	}
}