<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс-интерфейс для таблицы pivot_mail.mail_password_confirm_story
 */
class Gateway_Db_PivotMail_MailPasswordConfirmStory extends Gateway_Db_PivotMail_Main {

	protected const _TABLE_KEY = "mail_password_confirm_story";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @param Struct_Db_PivotMail_MailPasswordConfirmStory $story
	 *
	 * @return string
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotMail_MailPasswordConfirmStory $story):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"user_id"      => $story->user_id,
			"status"       => $story->status,
			"type"         => $story->type,
			"stage"        => $story->stage,
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
	 * метод для обновления записи
	 *
	 * @throws ParseFatalException
	 */
	public static function set(string $confirm_mail_password_story_id, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailPasswordStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `confirm_mail_password_story_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $confirm_mail_password_story_id, 1);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws ParseFatalException
	 */
	public static function setById(int $confirm_mail_password_story_id, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotMail_MailPasswordConfirmStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `confirm_mail_password_story_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $confirm_mail_password_story_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @param int $confirm_mail_password_story_id
	 *
	 * @return Struct_Db_PivotMail_MailPasswordConfirmStory
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $confirm_mail_password_story_id):Struct_Db_PivotMail_MailPasswordConfirmStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `confirm_mail_password_story_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $confirm_mail_password_story_id, 1);
		if (!isset($row["confirm_mail_password_story_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * метод для удаления записей (используется в тестах)
	 *
	 * @throws \parseException
	 */
	public static function deleteByUser(int $user_id, int $limit = 1):int {

		ServerProvider::assertTest();

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// формируем и осуществляем запрос
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, $limit);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * конвертируем запись в структуру
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotMail_MailPasswordConfirmStory
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotMail_MailPasswordConfirmStory {

		return new Struct_Db_PivotMail_MailPasswordConfirmStory(
			$row["confirm_mail_password_story_id"],
			$row["user_id"],
			$row["status"],
			$row["type"],
			$row["stage"],
			$row["error_count"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"],
			$row["session_uniq"],
		);
	}

	// получает таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
