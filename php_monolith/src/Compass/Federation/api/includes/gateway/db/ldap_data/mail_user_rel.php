<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Server\ServerProvider;

/**
 * класс для работы с таблицей ldap_data . mail_user_rel
 * @package Compass\Federation
 */
class Gateway_Db_LdapData_MailUserRel extends Gateway_Db_LdapData_Main
{
	protected const _TABLE_NAME = "mail_user_rel";

	/**
	 * Вставить запись
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function insert(Struct_Db_LdapData_MailUserRel $mail_user_rel): void
	{

		$insert_array = [
			"uid"         => $mail_user_rel->uid,
			"mail_source" => $mail_user_rel->mail_source,
			"mail"        => $mail_user_rel->mail,
			"created_at"  => $mail_user_rel->created_at,
			"updated_at"  => $mail_user_rel->updated_at,
		];

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array);
	}

	/**
	 * создаем запись
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function insertOrUpdate(Struct_Db_LdapData_MailUserRel $mail_user_rel): void
	{

		$insert_array = [
			"uid"         => $mail_user_rel->uid,
			"mail_source" => $mail_user_rel->mail_source,
			"mail"        => $mail_user_rel->mail,
			"created_at"  => $mail_user_rel->created_at,
			"updated_at"  => $mail_user_rel->updated_at,
		];

		$update_array = [
			"mail_source" => $mail_user_rel->mail_source,
			"mail"        => $mail_user_rel->mail,
			"updated_at"  => $mail_user_rel->updated_at,
		];

		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_NAME, $insert_array, $update_array);
	}

	/**
	 * получаем запись из базы по PK
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(string $uid): Struct_Db_LdapData_MailUserRel
	{

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `uid` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $uid, 1);

		if (!isset($row["uid"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_LdapData_MailUserRel::rowToStruct($row);
	}

	/**
	 * получаем запись из базы по адресу почты
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws RowNotFoundException
	 */
	public static function getByMail(string $mail): Struct_Db_LdapData_MailUserRel
	{

		// EXPLAIN INDEX=mail_UNIQUE
		$query = "SELECT * FROM `?p` WHERE `mail` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $mail, 1);

		if (!isset($row["uid"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_LdapData_MailUserRel::rowToStruct($row);
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
	 * Удалить запись
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws \parseException
	 */
	public static function delete(string $uid): void
	{

		ServerProvider::assertTest();

		$query = "DELETE FROM `?p` WHERE `uid` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_NAME, $uid, 1);
	}
}
