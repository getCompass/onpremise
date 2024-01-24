<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_rating_{10m}.action_raw_list_{1}
 */
class Gateway_Db_PivotRating_ActionRawList extends Gateway_Db_PivotRating_Main {

	protected const _TABLE_KEY = "action_raw_list";

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
	 * @param int $user_id
	 * @param int $space_id
	 * @param int $action_at
	 *
	 * @return bool
	 */
	public static function isExistByUserIdAndSpaceIdAndActionAt(int $user_id, int $space_id, int $action_at):bool {

		try {
			self::getByUserIdAndSpaceIdAndActionAt($user_id, $space_id, $action_at);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			return false;
		}

		return true;
	}

	/**
	 * Получаем одну запись по user_id, space_id и action_at
	 *
	 * @param int $user_id
	 * @param int $space_id
	 * @param int $action_at
	 *
	 * @return Struct_Db_PivotRating_ActionRaw
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getByUserIdAndSpaceIdAndActionAt(int $user_id, int $space_id, int $action_at):Struct_Db_PivotRating_ActionRaw {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `space_id` = ?i AND `action_at` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $space_id, $action_at, 1);

		return self::_rowToStruct($row);
	}

	/**
	 * Получаем список записей по action_at
	 *
	 * @param int $shard_id
	 * @param int $date_start
	 * @param int $date_end
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public static function getUserIdListBetweenActionAt(int $shard_id, int $date_start, int $date_end, int $limit, int $offset):array {

		$shard_key  = self::_getDbKey($shard_id);
		$table_name = self::_getTableKey($shard_id);

		// запрос проверен на EXPLAIN(INDEX=action_at)
		$query = "SELECT DISTINCT(user_id) as `user_id` FROM `?p` FORCE INDEX (`action_at`) WHERE `action_at` BETWEEN ?i AND ?i LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $date_start, $date_end, $limit, $offset);

		return array_column($list, "user_id");
	}

	/**
	 * Формируем массив для вставки в таблицу
	 *
	 * @param int   $user_id
	 * @param int   $space_id
	 * @param int   $action_at
	 * @param array $action_list
	 *
	 * @return array
	 */
	public static function makeInsertRow(int $user_id, int $space_id, int $action_at, array $action_list):array {

		return [
			"user_id"     => $user_id,
			"space_id"    => $space_id,
			"action_at"   => $action_at,
			"created_at"  => time(),
			"action_list" => $action_list,
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
	protected static function _rowToStruct(array $row):Struct_Db_PivotRating_ActionRaw {

		if (!isset($row["user_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("not found row");
		}

		return Struct_Db_PivotRating_ActionRaw::fromRow($row);
	}
}