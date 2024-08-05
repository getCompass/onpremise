<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_auth_{Y}.auth_ldap_list_{m}
 */
class Gateway_Db_PivotAuth_AuthLdapList extends Gateway_Db_PivotAuth_Main {

	protected const _TABLE_KEY = "auth_ldap_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotAuth_AuthLdap $auth_mail):void {

		$shard_key  = self::_getDbKey(Type_Pack_Auth::getShardId($auth_mail->auth_map));
		$table_name = self::_getTableKey(Type_Pack_Auth::getTableId($auth_mail->auth_map));

		$insert = [
			"auth_map"        => $auth_mail->auth_map,
			"ldap_auth_token" => $auth_mail->ldap_auth_token,
			"created_at"      => $auth_mail->created_at,
		];

		// осуществляем запрос
		ShardingGateway::database($shard_key)->insert($table_name, $insert);
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

			if (!property_exists(Struct_Db_PivotAuth_AuthLdap::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `auth_map` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $auth_map, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $auth_map):Struct_Db_PivotAuth_AuthLdap {

		$shard_key  = self::_getDbKey(Type_Pack_Auth::getShardId($auth_map));
		$table_name = self::_getTableKey(Type_Pack_Auth::getTableId($auth_map));

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `auth_map`=?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $auth_map, 1);

		if (!isset($row["auth_map"])) {
			throw new \cs_RowIsEmpty();
		}
		return Struct_Db_PivotAuth_AuthLdap::rowToStruct($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey(int $table_id):string {

		return self::_TABLE_KEY . "_" . $table_id;
	}
}