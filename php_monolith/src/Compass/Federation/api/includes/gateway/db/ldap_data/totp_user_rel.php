<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей ldap_data . ldap_totp_user_rel
 * @package Compass\Federation
 */
class Gateway_Db_LdapData_TotpUserRel extends Gateway_Db_LdapData_Main
{
	protected const _TABLE_NAME = "ldap_totp_user_rel";

	/**
	 * Вставить запись
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function insert(Struct_Db_LdapData_TotpUserRel $totp_user_rel): void
	{

		$insert_array = [
			"uid"                 => $totp_user_rel->uid,
			"crypted_totp_secret" => $totp_user_rel->crypted_totp_secret,
			"created_at"          => $totp_user_rel->created_at,
			"updated_at"          => $totp_user_rel->updated_at,
		];

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array);
	}

	/**
	 * Получить запись по PK
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(string $uid): Struct_Db_LdapData_TotpUserRel
	{

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `uid` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $uid, 1);

		if (!isset($row["uid"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_LdapData_TotpUserRel::rowToStruct($row);
	}

	/**
	 * обновляем запись в базе по PK
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function set(string $uid, array $set): int
	{

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `uid` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $uid, 1);
	}

	/**
	 * Удалить запись по PK
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function delete(string $uid): void
	{

		$query = "DELETE FROM `?p` WHERE `uid` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_NAME, $uid, 1);
	}
}
