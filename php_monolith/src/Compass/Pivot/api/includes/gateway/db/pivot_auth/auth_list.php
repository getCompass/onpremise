<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_auth_{Y}.auth_list_{m}
 */
class Gateway_Db_PivotAuth_AuthList extends Gateway_Db_PivotAuth_Main {

	protected const _TABLE_KEY = "auth_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotAuth_Auth $auth):string {

		$shard_key  = self::_getDbKey(Type_Pack_Auth::getShardIdByTime($auth->created_at));
		$table_name = self::_getTableKey(Type_Pack_Auth::getTableIdByTime($auth->created_at));

		$insert = [
			"auth_uniq"  => $auth->auth_uniq,
			"user_id"    => $auth->user_id,
			"is_success" => $auth->is_success,
			"type"       => $auth->type,
			"created_at" => $auth->created_at,
			"updated_at" => $auth->updated_at,
			"expires_at" => $auth->expires_at,
			"ua_hash"    => $auth->ua_hash,
			"ip_address" => $auth->ip_address,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(string $auth_map, array $set):int {

		$shard_key  = self::_getDbKey(Type_Pack_Auth::getShardId($auth_map));
		$table_name = self::_getTableKey(Type_Pack_Auth::getTableId($auth_map));

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotAuth_Auth::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		$auth_uniq = Type_Pack_Auth::getAuthUniq($auth_map);

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `auth_uniq` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $auth_uniq, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $auth_map):Struct_Db_PivotAuth_Auth {

		$shard_key  = self::_getDbKey(Type_Pack_Auth::getShardId($auth_map));
		$table_name = self::_getTableKey(Type_Pack_Auth::getTableId($auth_map));

		$auth_uniq = Type_Pack_Auth::getAuthUniq($auth_map);

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `auth_uniq`=?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $auth_uniq, 1);
		if (!isset($row["auth_uniq"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_PivotAuth_Auth(
			$row["auth_uniq"],
			$row["user_id"],
			$row["is_success"],
			$row["type"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"],
			$row["ua_hash"],
			$row["ip_address"],
		);
	}

	/**
	 * Возвращает количество неиспользованных записей за период.
	 */
	public static function getUnusedCountPerPeriod(int $date_from, int $date_to):int {

		$shard_key  = self::_getDbKey(Type_Pack_TwoFa::getShardIdByTime($date_from));
		$table_name = self::_getTableKey(Type_Pack_TwoFa::getTableIdByTime($date_from));

		// explain index get_unused
		$query  = "SELECT COUNT(*) as `count` FROM `?p` WHERE `expires_at` BETWEEN ?i AND ?i AND `is_success` = ?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $date_from, $date_to, 0, 1);

		return (int) $result["count"];
	}

	/**
	 * Возвращает количество записей за период
	 */
	public static function getTotalCountPerPeriod(int $date_from, int $date_to):int {

		$shard_key  = self::_getDbKey(Type_Pack_TwoFa::getShardIdByTime($date_from));
		$table_name = self::_getTableKey(Type_Pack_TwoFa::getTableIdByTime($date_from));

		// explain index get_unused
		$query  = "SELECT COUNT(*) as `count` FROM `?p` WHERE `expires_at` BETWEEN ?i AND ?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $date_from, $date_to, 1);

		return (int) $result["count"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey(int $table_id):string {

		return self::_TABLE_KEY . "_" . $table_id;
	}
}