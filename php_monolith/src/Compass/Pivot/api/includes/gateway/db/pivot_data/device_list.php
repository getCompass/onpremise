<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_data.device_list_{0-f}
 */
class Gateway_Db_PivotData_DeviceList extends Gateway_Db_PivotData_Main {

	protected const _TABLE_KEY = "device_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(string $device_id, array $insert):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($device_id);

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 */
	public static function set(string $device_id, array $set):void {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($device_id);

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `device_id` = ?s LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $device_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 */
	public static function getOne(string $device_id):array {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($device_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `device_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $device_id, 1);

		if (!isset($row["device_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("device not found");
		}

		return self::_doFromJsonIfNeed($row);
	}

	/**
	 * метод для получения записи на обновление
	 *
	 */
	public static function getForUpdate(string $device_id):array {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($device_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `device_id` = ?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $device_id, 1);

		if (!isset($row["device_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("device not found");
		}

		return self::_doFromJsonIfNeed($row);
	}

	/**
	 * метод для получения записей по списку device_id_list
	 *
	 */
	public static function getAllByDeviceIdList(array $device_id_list):array {

		$shard_key = self::_getDbKey();

		$device_id_list_by_table = [];

		foreach ($device_id_list as $device_id) {

			$table_name                             = self::_getTableKey($device_id);
			$device_id_list_by_table[$table_name][] = $device_id;
		}

		$all_list = [];
		foreach ($device_id_list_by_table as $table_name => $device_id_list) {

			// формируем и осуществляем запрос
			$query = "SELECT * FROM `?p` WHERE `device_id` IN (?a) LIMIT ?i";
			$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $device_id_list, count($device_id_list));

			$all_list = array_merge($all_list, $list);
		}

		foreach ($all_list as $k => $v) {
			$all_list[$k] = self::_doFromJsonIfNeed($v);
		}

		return $all_list;
	}

	/**
	 * метод для получения записей по user_id
	 *
	 */
	public static function getAllByUserId(int $user_id, int $limit):array {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $limit);

		foreach ($list as $k => $v) {
			$list[$k] = self::_doFromJsonIfNeed($v);
		}

		return $list;
	}

	/**
	 * получаем количество записей по user_id
	 *
	 */
	public static function getCountByUserId(int $user_id):int {

		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1)["count"] ?? 0;
	}

	/**
	 * удалить девайс из базы
	 *
	 */
	public static function delete(string $device_id):void {

		$table_name = self::_getTableKey($device_id);
		$query      = "DELETE FROM `?p` WHERE `device_id` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, $table_name, $device_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получает таблицу
	 *
	 */
	protected static function _getTableKey(string $device_id):string {

		return self::_TABLE_KEY . "_" . strtolower(substr($device_id, -1));
	}

	/**
	 * преобразуем json в массив
	 *
	 */
	protected static function _doFromJsonIfNeed(array $row):array {

		if (isset($row["extra"])) {
			$row["extra"] = fromJson($row["extra"]);
		}

		return $row;
	}
}