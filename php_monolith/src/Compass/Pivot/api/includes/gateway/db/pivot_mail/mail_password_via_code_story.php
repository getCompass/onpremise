<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_mail.mail_password_via_code_story
 */
class Gateway_Db_PivotMail_MailPasswordViaCodeStory extends Gateway_Db_PivotMail_Main {

	protected const _TABLE_KEY = "mail_password_via_code_story";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 * @throws ParseFatalException
	 */
	public static function insert(Struct_Db_PivotMail_MailPasswordViaCodeStory $story):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"password_mail_story_id" => $story->password_mail_story_id,
			"mail"                   => $story->mail,
			"status"                 => $story->status,
			"type"                   => $story->status,
			"stage"                  => $story->stage,
			"resend_count"           => $story->resend_count,
			"error_count"            => $story->error_count,
			"created_at"             => $story->created_at,
			"updated_at"             => $story->updated_at,
			"next_resend_at"         => $story->next_resend_at,
			"message_id"             => $story->message_id,
			"code_hash"              => $story->code_hash,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws ParseFatalException
	 */
	public static function setById(string $password_mail_story_id, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailPasswordViaCodeStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		//  EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `password_mail_story_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $password_mail_story_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws ParseFatalException
	 */
	public static function getOne(string $password_mail_story_map):Struct_Db_PivotMail_MailPasswordViaCodeStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		try {
			$password_mail_story_id = Type_Pack_PasswordMailStory::getId($password_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `password_mail_story_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $password_mail_story_id, 1);
		if (!isset($row["password_mail_story_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Конвертируем запись в структуру
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotMail_MailPasswordViaCodeStory {

		return new Struct_Db_PivotMail_MailPasswordViaCodeStory(
			$row["password_mail_story_id"],
			$row["mail"],
			$row["status"],
			$row["type"],
			$row["stage"],
			$row["resend_count"],
			$row["error_count"],
			$row["created_at"],
			$row["updated_at"],
			$row["next_resend_at"],
			$row["message_id"],
			$row["code_hash"],
		);
	}

	/**
	 * Получает таблицу
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
