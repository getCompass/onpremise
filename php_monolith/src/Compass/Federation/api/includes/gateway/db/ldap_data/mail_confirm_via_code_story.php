<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей ldap_data . mail_confirm_via_code_story
 * @package Compass\Federation
 */
class Gateway_Db_LdapData_MailConfirmViaCodeStory extends Gateway_Db_LdapData_Main {

	protected const _TABLE_NAME = "mail_confirm_via_code_story";

	/**
	 * создаем запись
	 *
	 * @param Struct_Db_LdapData_MailConfirmViaCodeStory $mail_confirm_via_code_story
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function insert(Struct_Db_LdapData_MailConfirmViaCodeStory $mail_confirm_via_code_story):int {

		$insert_array = [
			"id"                    => $mail_confirm_via_code_story->id,
			"status"                => $mail_confirm_via_code_story->status,
			"resend_count"          => $mail_confirm_via_code_story->resend_count,
			"error_count"           => $mail_confirm_via_code_story->error_count,
			"created_at"            => $mail_confirm_via_code_story->created_at,
			"updated_at"            => $mail_confirm_via_code_story->updated_at,
			"next_resend_at"        => $mail_confirm_via_code_story->next_resend_at,
			"mail_confirm_story_id" => $mail_confirm_via_code_story->mail_confirm_story_id,
			"message_id"            => $mail_confirm_via_code_story->message_id,
			"code_hash"             => $mail_confirm_via_code_story->code_hash,
			"mail"                  => $mail_confirm_via_code_story->mail,
		];

		return (int) ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
	}

	/**
	 * получаем запись из базы по PK
	 *
	 * @param int $mail_confirm_story_id
	 *
	 * @return Struct_Db_LdapData_MailConfirmViaCodeStory
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws RowNotFoundException
	 */
	public static function getLastByMailConfirmStoryId(int $mail_confirm_story_id):Struct_Db_LdapData_MailConfirmViaCodeStory {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `mail_confirm_story_id` = ?i ORDER BY `id` DESC LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $mail_confirm_story_id, 1);

		if (!isset($row["id"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_LdapData_MailConfirmViaCodeStory::rowToStruct($row);
	}

	/**
	 * получаем все записи, относящиеся к одному процессу входа
	 *
	 * @param int $mail_confirm_story_id
	 *
	 * @return Struct_Db_LdapData_MailConfirmViaCodeStory[]
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getByMailConfirmStoryId(int $mail_confirm_story_id):array {

		// EXPLAIN PRIMARY KEY
		$query  = "SELECT * FROM `?p` WHERE `mail_confirm_story_id` = ?i ORDER BY `id` DESC LIMIT ?i";
		$result = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_NAME, $mail_confirm_story_id, 100);

		return array_map(fn(array $row) => Struct_Db_LdapData_MailConfirmViaCodeStory::rowToStruct($row), $result);
	}

	/**
	 * обновляем запись в базе по PK
	 *
	 * @param int   $id
	 * @param array $set
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function set(int $id, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $id, 1);
	}
}