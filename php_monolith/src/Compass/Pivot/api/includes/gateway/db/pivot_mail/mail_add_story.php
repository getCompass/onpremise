<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_mail.mail_add_story
 */
class Gateway_Db_PivotMail_MailAddStory extends Gateway_Db_PivotMail_Main {

	protected const _TABLE_KEY = "mail_add_story";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotMail_MailAddStory $story):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"user_id"      => $story->user_id,
			"stage"        => $story->stage,
			"updated_at"   => $story->updated_at,
			"created_at"   => $story->created_at,
			"status"       => $story->status,
			"error_count"  => $story->error_count,
			"expires_at"   => $story->expires_at,
			"session_uniq" => $story->session_uniq,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(string $add_mail_story_map, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		try {
			$add_mail_story_id = Type_Pack_AddMailStory::getId($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailAddStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `add_mail_story_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $add_mail_story_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $add_mail_story_map):Struct_Db_PivotMail_MailAddStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		try {
			$add_mail_story_id = Type_Pack_AddMailStory::getId($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `add_mail_story_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $add_mail_story_id, 1);
		if (!isset($row["add_mail_story_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * метод для получения записи на обновление
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(string $add_mail_story_map):Struct_Db_PivotMail_MailAddStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		try {
			$add_mail_story_id = Type_Pack_AddMailStory::getId($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `add_mail_story_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $add_mail_story_id, 1);
		if (!isset($row["add_mail_story_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws ParseFatalException
	 */
	public static function setById(int $add_mail_story_id, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailPasswordStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `add_mail_story_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $add_mail_story_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotMail_MailAddStory
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotMail_MailAddStory {

		return new Struct_Db_PivotMail_MailAddStory(
			$row["add_mail_story_id"],
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
	 * получает таблицу
	 *
	 * @return string
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
