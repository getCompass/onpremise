<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы pivot_mail.mail_change_story
 */
class Gateway_Db_PivotMail_MailChangeStory extends Gateway_Db_PivotMail_Main {

	protected const _TABLE_KEY = "mail_change_story";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод вставки записи в базу
	 *
	 * @throws \queryException
	 * @throws ParseFatalException
	 */
	public static function insert(Struct_Db_PivotMail_MailChangeStory $story):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"user_id"      => $story->user_id,
			"stage"        => $story->stage,
			"status"       => $story->status,
			"updated_at"   => $story->updated_at,
			"created_at"   => $story->created_at,
			"error_count"  => $story->error_count,
			"expires_at"   => $story->expires_at,
			"session_uniq" => $story->session_uniq,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * Метод для обновления записи
	 *
	 * @throws ParseFatalException
	 */
	public static function setById(string $change_mail_story_id, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailChangeStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `change_mail_story_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $change_mail_story_id, 1);
	}

	/**
	 * Метод для обновления записи
	 *
	 * @throws ParseFatalException
	 */
	public static function set(string $change_mail_story_map, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		try {
			$change_mail_story_id = Type_Pack_ChangeMailStory::getId($change_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailChangeStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `change_mail_story_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $change_mail_story_id, 1);
	}

	/**
	 * Метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws ParseFatalException
	 */
	public static function getOne(string $change_mail_story_map):Struct_Db_PivotMail_MailChangeStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		try {
			$change_mail_story_id = Type_Pack_ChangeMailStory::getId($change_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `change_mail_story_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $change_mail_story_id, 1);
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
	protected static function _rowToStruct(array $row):Struct_Db_PivotMail_MailChangeStory {

		return new Struct_Db_PivotMail_MailChangeStory(
			$row["change_mail_story_id"],
			$row["user_id"],
			$row["status"],
			$row["stage"],
			$row["created_at"],
			$row["updated_at"],
			$row["error_count"],
			$row["expires_at"],
			$row["session_uniq"],
		);
	}

	/**
	 * Получает таблицу
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
