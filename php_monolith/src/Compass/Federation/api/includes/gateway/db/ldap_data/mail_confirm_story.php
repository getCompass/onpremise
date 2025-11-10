<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей ldap_data . mail_confirm_story
 * @package Compass\Federation
 */
class Gateway_Db_LdapData_MailConfirmStory extends Gateway_Db_LdapData_Main {

	protected const _TABLE_NAME = "mail_confirm_story";

	/**
	 * создаем запись
	 *
	 * @param Struct_Db_LdapData_MailConfirmStory $mail_confirm_story
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function insert(Struct_Db_LdapData_MailConfirmStory $mail_confirm_story):int {

		$insert_array = [
			"mail_confirm_story_id" => $mail_confirm_story->mail_confirm_story_id,
			"status"                => $mail_confirm_story->status,
			"stage"                 => $mail_confirm_story->stage,
			"created_at"            => $mail_confirm_story->created_at,
			"updated_at"            => $mail_confirm_story->updated_at,
			"expires_at"            => $mail_confirm_story->expires_at,
			"ldap_auth_token"       => $mail_confirm_story->ldap_auth_token,
			"uid"                   => $mail_confirm_story->uid,
		];

		return (int) ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
	}

	/**
	 * получаем запись из базы по PK
	 *
	 * @param int $mail_confirm_story_id
	 *
	 * @return Struct_Db_LdapData_MailConfirmStory
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(int $mail_confirm_story_id):Struct_Db_LdapData_MailConfirmStory {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `mail_confirm_story_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $mail_confirm_story_id, 1);

		if (!isset($row["mail_confirm_story_id"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_LdapData_MailConfirmStory::rowToStruct($row);
	}

	/**
	 * обновляем запись в базе по PK
	 *
	 * @param int   $mail_confirm_story_id
	 * @param array $set
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function set(int $mail_confirm_story_id, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `mail_confirm_story_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $mail_confirm_story_id, 1);
	}
}