<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей ldap_data . auth_list
 * @package Compass\Federation
 */
class Gateway_Db_LdapData_LdapAuthList extends Gateway_Db_LdapData_Main {

	protected const _TABLE_NAME = "ldap_auth_list";

	/**
	 * создаем запись
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_LdapData_LdapAuth $auth):void {

		$insert_array = [
			"ldap_auth_token" => $auth->ldap_auth_token,
			"status"          => $auth->status,
			"created_at"      => $auth->created_at,
			"updated_at"      => $auth->updated_at,
			"uid"             => $auth->uid,
			"username"        => $auth->username,
			"dn"              => $auth->dn,
			"data"            => json_encode($auth->data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE),
		];
		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
	}

	/**
	 * получаем запись из базы по PK
	 *
	 * @return Struct_Db_LdapData_LdapAuth
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(string $ldap_auth_token):Struct_Db_LdapData_LdapAuth {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `ldap_auth_token` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $ldap_auth_token, 1);

		if (!isset($row["ldap_auth_token"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_LdapData_LdapAuth::rowToStruct($row);
	}

	/**
	 * обновляем запись в базе по PK
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $ldap_auth_token, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `ldap_auth_token` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $ldap_auth_token, 1);
	}
}