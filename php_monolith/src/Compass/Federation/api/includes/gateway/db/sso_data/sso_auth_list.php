<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей sso_data . auth_list
 * @package Compass\Federation
 */
class Gateway_Db_SsoData_SsoAuthList extends Gateway_Db_SsoData_Main {

	protected const _TABLE_NAME = "sso_auth_list";

	/**
	 * создаем запись
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_SsoData_SsoAuth $auth):void {

		$insert_array = [
			"sso_auth_token" => $auth->sso_auth_token,
			"signature"      => $auth->signature,
			"status"         => $auth->status,
			"expires_at"     => $auth->expires_at,
			"completed_at"   => $auth->completed_at,
			"created_at"     => $auth->created_at,
			"updated_at"     => $auth->updated_at,
			"link"           => $auth->link,
			"ua_hash"        => $auth->ua_hash,
			"ip_address"     => $auth->ip_address,
		];
		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
	}

	/**
	 * получаем запись из базы по PK
	 *
	 * @return Struct_Db_SsoData_SsoAuth
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(string $sso_auth_token):Struct_Db_SsoData_SsoAuth {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `sso_auth_token` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $sso_auth_token, 1);

		if (!isset($row["sso_auth_token"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_SsoData_SsoAuth::rowToStruct($row);
	}

	/**
	 * обновляем запись в базе по PK
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $sso_auth_token, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `sso_auth_token` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $sso_auth_token, 1);
	}
}