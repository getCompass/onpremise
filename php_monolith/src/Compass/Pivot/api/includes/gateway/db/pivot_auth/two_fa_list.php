<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_auth_{Y}.2fa_list_{m}
 */
class Gateway_Db_PivotAuth_TwoFaList extends Gateway_Db_PivotAuth_Main {

	protected const _TABLE_KEY = "2fa_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotAuth_TwoFa $two_fa):string {

		$shard_key  = self::_getDbKey(Type_Pack_TwoFa::getShardIdByTime($two_fa->created_at));
		$table_name = self::_getTableKey(Type_Pack_TwoFa::getTableIdByTime($two_fa->created_at));

		$insert = [
			"2fa_map"     => $two_fa->two_fa_map,
			"user_id"     => $two_fa->user_id,
			"company_id"  => $two_fa->company_id,
			"is_active"   => $two_fa->is_active,
			"is_success"  => $two_fa->is_success,
			"action_type" => $two_fa->action_type,
			"created_at"  => $two_fa->created_at,
			"updated_at"  => $two_fa->updated_at,
			"expires_at"  => $two_fa->expires_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function set(string $two_fa_map, array $set):int {

		$shard_key  = self::_getDbKey(Type_Pack_TwoFa::getShardId($two_fa_map));
		$table_name = self::_getTableKey(Type_Pack_TwoFa::getTableId($two_fa_map));

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotAuth_TwoFa::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `2fa_map` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $two_fa_map, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getOne(string $two_fa_map):Struct_Db_PivotAuth_TwoFa {

		$shard_key  = self::_getDbKey(Type_Pack_TwoFa::getShardId($two_fa_map));
		$table_name = self::_getTableKey(Type_Pack_TwoFa::getTableId($two_fa_map));

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `2fa_map`=?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $two_fa_map, 1);
		if (!isset($row["2fa_map"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_PivotAuth_TwoFa(
			$row["2fa_map"],
			$row["user_id"],
			$row["company_id"],
			$row["is_active"],
			$row["is_success"],
			$row["action_type"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"],
		);
	}

	/**
	 * получение последнего 2fa пользователя (по типу и компании)
	 *
	 * @param int $user_id
	 * @param int $action_type
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotAuth_TwoFa
	 * @throws \parseException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getLastByUserAndType(int $user_id, int $action_type, int $company_id):Struct_Db_PivotAuth_TwoFa {

		$shard_key  = self::_getDbKey(Type_Pack_TwoFa::getShardIdByTime(time()));
		$table_name = self::_getTableKey(Type_Pack_TwoFa::getTableIdByTime(time()));

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=user_company)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i AND `action_type`=?i AND `company_id`=?i ORDER BY `created_at` DESC LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $action_type, $company_id, 1);
		if (!isset($row["2fa_map"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_Db_PivotAuth_TwoFa(
			$row["2fa_map"],
			$row["user_id"],
			$row["company_id"],
			$row["is_active"],
			$row["is_success"],
			$row["action_type"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"],
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

	/**
	 * метод для удаления записей (используется в тестах)
	 *
	 * @throws \parseException
	 */
	public static function deleteByUser(int $user_id, int $limit = 1):int {

		assertTestServer();

		$shard_key  = self::_getDbKey(Type_Pack_TwoFa::getShardIdByTime(time()));
		$table_name = self::_getTableKey(Type_Pack_TwoFa::getTableIdByTime(time()));

		// формируем и осуществляем запрос
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, $limit);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey(int $table_id):string {

		return self::_TABLE_KEY . "_" . $table_id;
	}
}
