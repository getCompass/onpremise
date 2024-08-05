<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей sso_data . account_user_rel
 * @package Compass\Federation
 */
class Gateway_Db_SsoData_SsoAccountUserRel extends Gateway_Db_SsoData_Main {

	protected const _TABLE_NAME = "sso_account_user_rel";

	/**
	 * создаем запись
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 * @throws RowDuplicationException
	 */
	public static function insert(Struct_Db_SsoData_SsoAccountUserRel $account_user_rel):void {

		$insert_array = [
			"sub_hash"   => $account_user_rel->sub_hash,
			"user_id"    => $account_user_rel->user_id,
			"sub_plain"  => $account_user_rel->sub_plain,
			"created_at" => $account_user_rel->created_at,
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
	 * @return Struct_Db_SsoData_SsoAccountUserRel
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(string $sub_hash):Struct_Db_SsoData_SsoAccountUserRel {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `sub_hash` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $sub_hash, 1);

		if (!isset($row["sub_hash"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_SsoData_SsoAccountUserRel::rowToStruct($row);
	}

	/**
	 * обновляем запись в базе по PK
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $sub_hash, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `sub_hash` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $sub_hash, 1);
	}

	/**
	 * получаем запись из базы по user_id (UNIQUE KEY)
	 *
	 * @return Struct_Db_SsoData_SsoAccountUserRel
	 * @throws RowNotFoundException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getOneByUserID(int $user_id):Struct_Db_SsoData_SsoAccountUserRel {

		// EXPLAIN user_id_UNIQUE
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $user_id, 1);

		if (!isset($row["sub_hash"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_SsoData_SsoAccountUserRel::rowToStruct($row);
	}

	/**
	 * удаляем запись в базе по PK
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function delete(string $sub_hash):void {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `sub_hash` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_NAME, $sub_hash, 1);
	}
}