<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_mail.mail_add_via_code_story
 */
class Gateway_Db_PivotMail_MailAddViaCodeStory extends Gateway_Db_PivotMail_Main {

	protected const _TABLE_KEY = "mail_add_via_code_story";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotMail_MailAddViaCodeStory $story):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"add_mail_story_id" => $story->add_mail_story_id,
			"mail"              => $story->mail,
			"status"            => $story->status,
			"stage"             => $story->stage,
			"resend_count"      => $story->resend_count,
			"error_count"       => $story->error_count,
			"created_at"        => $story->created_at,
			"updated_at"        => $story->updated_at,
			"next_resend_at"    => $story->next_resend_at,
			"message_id"        => $story->message_id,
			"code_hash"         => $story->code_hash,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 */
	public static function set(string $add_mail_story_map, string $mail, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		try {
			$add_mail_story_id = Type_Pack_AddMailStory::getId($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailAddViaCodeStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `add_mail_story_id` = ?i AND `mail` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $add_mail_story_id, $mail, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @return Struct_Db_PivotMail_MailAddViaCodeStory
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $add_mail_story_map, string $mail):Struct_Db_PivotMail_MailAddViaCodeStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		try {
			$add_mail_story_id = Type_Pack_AddMailStory::getId($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `add_mail_story_id` = ?i AND `mail` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $add_mail_story_id, $mail, 1);
		if (!isset($row["add_mail_story_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotMail_MailAddViaCodeStory
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotMail_MailAddViaCodeStory {

		return new Struct_Db_PivotMail_MailAddViaCodeStory(
			$row["add_mail_story_id"],
			$row["mail"],
			$row["status"],
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

	// получает таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
