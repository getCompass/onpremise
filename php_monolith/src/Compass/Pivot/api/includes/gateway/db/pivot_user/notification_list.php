<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.notification_list_{ceil}
 */
class Gateway_Db_PivotUser_NotificationList extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "notification_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(int $user_id, array $insert):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 */
	public static function set(int $user_id, array $set):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id):Struct_Db_PivotUser_Notification {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row = self::_doFromJsonIfNeed($row);
		return self::_rowToStruct($row);
	}

	/**
	 * метод для получения записи на обновление
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $user_id):Struct_Db_PivotUser_Notification {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row = self::_doFromJsonIfNeed($row);
		return self::_rowToStruct($row);
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
	 * преобразуем json в массив
	 *
	 */
	protected static function _doFromJsonIfNeed(array $row):array {

		if (isset($row["device_list"])) {
			$row["device_list"] = fromJson($row["device_list"]);
		}

		if (isset($row["extra"])) {
			$row["extra"] = fromJson($row["extra"]);
		}

		return $row;
	}

	/**
	 * преобразуем строку записи базы в объект
	 *
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotUser_Notification {

		return new Struct_Db_PivotUser_Notification(
			$row["user_id"],
			$row["snoozed_until"],
			$row["created_at"],
			$row["updated_at"],
			$row["device_list"],
			$row["extra"]
		);
	}
}