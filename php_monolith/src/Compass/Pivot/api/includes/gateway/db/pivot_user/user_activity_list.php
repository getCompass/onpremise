<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.user_activity_list_{1}
 */
class Gateway_Db_PivotUser_UserActivityList extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "user_activity_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 * @mixed
	 */
	public static function insertOrUpdate(
		int $user_id,
		int $status,
		int $created_at,
		int $updated_at,
		int $last_ws_ping_at,
	):string {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = [
			"user_id"         => $user_id,
			"status"          => $status,
			"created_at"      => $created_at,
			"updated_at"      => $updated_at,
			"last_ws_ping_at" => $last_ws_ping_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insertOrUpdate($table_name, $insert);
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

			if (!property_exists(Struct_Db_PivotUser_UserActivityList::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, 1);
	}

	/**
	 * Метод для получения записи пользователя
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_PivotUser_UserActivityList
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id):Struct_Db_PivotUser_UserActivityList {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return Struct_Db_PivotUser_UserActivityList::rowToStruct($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}
}