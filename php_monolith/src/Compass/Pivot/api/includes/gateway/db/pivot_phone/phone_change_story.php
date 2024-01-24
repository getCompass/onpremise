<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_phone.phone_change_story
 */
class Gateway_Db_PivotPhone_PhoneChangeStory extends Gateway_Db_PivotPhone_Main {

	protected const _TABLE_KEY = "phone_change_story";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotPhone_PhoneChangeStory $story):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"user_id"      => $story->user_id,
			"stage"        => $story->stage,
			"updated_at"   => $story->updated_at,
			"created_at"   => $story->created_at,
			"status"       => $story->status,
			"expires_at"   => $story->expires_at,
			"session_uniq" => $story->session_uniq,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function set(string $change_phone_story_map, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$change_phone_story_id = Type_Pack_ChangePhoneStory::getId($change_phone_story_map);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotPhone_PhoneChangeStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `change_phone_story_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $change_phone_story_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getOne(string $change_phone_story_map):Struct_Db_PivotPhone_PhoneChangeStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$change_phone_story_id = Type_Pack_ChangePhoneStory::getId($change_phone_story_map);

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `change_phone_story_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $change_phone_story_id, 1);
		if (!isset($row["change_phone_story_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_PivotPhone_PhoneChangeStory(
			$row["change_phone_story_id"],
			$row["user_id"],
			$row["status"],
			$row["stage"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"],
			$row["session_uniq"],
		);
	}

	/**
	 * Возвращает общее количество записей за период.
	 */
	public static function getCountPerPeriod(int $date_from, int $date_to):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// explain index get_unused
		$query  = "SELECT COUNT(*) as `count` FROM `?p` WHERE `expires_at` BETWEEN ?i AND ?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $date_from, $date_to, 1);

		return (int) $result["count"];
	}

	/**
	 * Возвращает количество записей в указанных статусах за период.
	 */
	public static function getCountWithStatusPerPeriod(int $date_from, int $date_to, array $status_list):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// explain index get_unused
		$query  = "SELECT COUNT(*) as `count` FROM `?p` WHERE `expires_at` BETWEEN ?i AND ?i AND STATUS IN (?a) LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $date_from, $date_to, $status_list, 1);

		return (int) $result["count"];
	}

	/**
	 * метод для получения записи для пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getOneForUser(int $user_id, string $change_phone_story_map):Struct_Db_PivotPhone_PhoneChangeStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$change_phone_story_id = Type_Pack_ChangePhoneStory::getId($change_phone_story_map);

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `change_phone_story_id` = ?i AND `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $change_phone_story_id, $user_id, 1);
		if (!isset($row["change_phone_story_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_PivotPhone_PhoneChangeStory(
			$row["change_phone_story_id"],
			$row["user_id"],
			$row["status"],
			$row["stage"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"],
			$row["session_uniq"],
		);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
