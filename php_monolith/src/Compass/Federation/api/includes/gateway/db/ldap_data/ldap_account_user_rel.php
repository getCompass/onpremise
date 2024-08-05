<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей ldap_data . ldap_account_user_rel
 * @package Compass\Federation
 */
class Gateway_Db_LdapData_LdapAccountUserRel extends Gateway_Db_LdapData_Main {

	protected const _TABLE_NAME = "ldap_account_user_rel";

	/**
	 * создаем запись
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 * @throws RowDuplicationException
	 */
	public static function insert(Struct_Db_LdapData_LdapAccountUserRel $account_user_rel):void {

		$insert_array = [
			"uid"        => $account_user_rel->uid,
			"user_id"    => $account_user_rel->user_id,
			"status"     => $account_user_rel->status,
			"created_at" => $account_user_rel->created_at,
			"updated_at" => $account_user_rel->updated_at,
			"username"   => $account_user_rel->username,
			"dn"         => $account_user_rel->dn,
		];
		try {
			ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
		} catch (\PDOException $e) {

			// если это дупликат
			if ($e->getCode() == 23000) {
				throw new RowDuplicationException("row duplication");
			}

			throw $e;
		}
	}

	/**
	 * получаем запись из базы по PK
	 *
	 * @return Struct_Db_LdapData_LdapAccountUserRel
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(string $uid):Struct_Db_LdapData_LdapAccountUserRel {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `uid` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $uid, 1);

		if (!isset($row["uid"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_LdapData_LdapAccountUserRel::rowToStruct($row);
	}

	/**
	 * обновляем запись в базе по PK
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $uid, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `uid` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $uid, 1);
	}

	/**
	 * получаем запись из базы по user_id (UNIQUE KEY)
	 *
	 * @return Struct_Db_LdapData_LdapAccountUserRel
	 * @throws RowNotFoundException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getOneByUserID(int $user_id):Struct_Db_LdapData_LdapAccountUserRel {

		// EXPLAIN user_id_UNIQUE
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $user_id, 1);

		if (!isset($row["uid"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_LdapData_LdapAccountUserRel::rowToStruct($row);
	}

	/**
	 * получаем все записи таблицы
	 *
	 * @return Struct_Db_LdapData_LdapAccountUserRel[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getAll():array {

		// получаем кол-во записей
		$count_query = "SELECT COUNT(*) as `count` FROM `?p` WHERE true LIMIT ?i";
		$count       = ShardingGateway::database(self::_DB_KEY)->getOne($count_query, self::_TABLE_NAME, 1)["count"] ?? 0;

		// EXPLAIN PRIMARY
		$query = "SELECT * FROM `?p` WHERE true LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_NAME, $count);

		return array_map(static fn(array $row) => Struct_Db_LdapData_LdapAccountUserRel::rowToStruct($row), $list);
	}

	/**
	 * удаляем запись в базе по PK
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function delete(string $uid):void {

		// EXPLAIN PRIMARY KEY
		$query = "DELETE FROM `?p` WHERE `uid` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_NAME, $uid, 1);
	}
}