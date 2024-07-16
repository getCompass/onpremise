<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_mail.mail_change_via_code_story
 */
class Gateway_Db_PivotMail_MailChangeViaCodeStory extends Gateway_Db_PivotMail_Main {

	protected const _TABLE_KEY = "mail_change_via_code_story";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 * @throws ParseFatalException
	 */
	public static function insert(Struct_Db_PivotMail_MailChangeViaCodeStory $story):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"change_mail_story_id" => $story->change_mail_story_id,
			"mail"                 => $story->mail,
			"mail_new"             => $story->mail_new,
			"status"               => $story->status,
			"stage"                => $story->stage,
			"resend_count"         => $story->resend_count,
			"error_count"          => $story->error_count,
			"created_at"           => $story->created_at,
			"updated_at"           => $story->updated_at,
			"next_resend_at"       => $story->next_resend_at,
			"message_id"           => $story->message_id,
			"code_hash"            => $story->code_hash,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * Метод для обновления записи
	 *
	 * @throws ParseFatalException
	 */
	public static function setById(int $change_mail_story_id, string $mail, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailChangeViaCodeStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `change_mail_story_id` = ?i AND `mail` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $change_mail_story_id, $mail, 1);
	}

	/**
	 * Метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws ParseFatalException
	 */
	public static function getOne(string $change_mail_story_map, string $mail):Struct_Db_PivotMail_MailChangeViaCodeStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		try {
			$change_mail_story_id = Type_Pack_ChangeMailStory::getId($change_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `change_mail_story_id` = ?i AND `mail` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $change_mail_story_id, $mail, 1);
		if (!isset($row["change_mail_story_id"])) {
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
	protected static function _rowToStruct(array $row):Struct_Db_PivotMail_MailChangeViaCodeStory {

		return new Struct_Db_PivotMail_MailChangeViaCodeStory(
			$row["change_mail_story_id"],
			$row["mail"],
			$row["mail_new"],
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

	/**
	 * Получает таблицу
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
