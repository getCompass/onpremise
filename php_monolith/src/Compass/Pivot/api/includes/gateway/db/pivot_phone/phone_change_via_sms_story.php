<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_phone.phone_change_via_sms_story
 */
class Gateway_Db_PivotPhone_PhoneChangeViaSmsStory extends Gateway_Db_PivotPhone_Main {

	protected const _TABLE_KEY = "phone_change_via_sms_story";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotPhone_PhoneChangeViaSmsStory $sms_story):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"change_phone_story_id" => $sms_story->change_phone_story_id,
			"phone_number"          => $sms_story->phone_number,
			"status"                => $sms_story->status,
			"stage"                 => $sms_story->stage,
			"resend_count"          => $sms_story->resend_count,
			"error_count"           => $sms_story->error_count,
			"created_at"            => $sms_story->created_at,
			"updated_at"            => $sms_story->updated_at,
			"next_resend_at"        => $sms_story->next_resend_at,
			"sms_id"                => $sms_story->sms_id,
			"sms_code_hash"         => $sms_story->sms_code_hash,
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
	public static function set(string $change_phone_story_map, string $phone_number, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$change_phone_story_id = Type_Pack_ChangePhoneStory::getId($change_phone_story_map);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotPhone_PhoneChangeViaSmsStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `change_phone_story_id` = ?i AND `phone_number` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $change_phone_story_id, $phone_number, 1);
	}

	/**
	 * метод для обновления записей текущего года по номеру телефона
	 *
	 * @throws \parseException
	 */
	public static function setForUserCurrentYear(string $phone_number, array $set):int {

		assertTestServer();

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotPhone_PhoneChangeViaSmsStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `phone_number`=?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $phone_number, 10000);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getOne(string $change_phone_story_map, string $phone_number, int $stage):Struct_Db_PivotPhone_PhoneChangeViaSmsStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$change_phone_story_id = Type_Pack_ChangePhoneStory::getId($change_phone_story_map);

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `change_phone_story_id` = ?i AND `phone_number` = ?s AND `stage` =?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $change_phone_story_id, $phone_number, $stage, 1);
		if (!isset($row["change_phone_story_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getOneWithStatus(string $change_phone_story_map, int $stage, int $status):Struct_Db_PivotPhone_PhoneChangeViaSmsStory {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$change_phone_story_id = Type_Pack_ChangePhoneStory::getId($change_phone_story_map);

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `change_phone_story_id` = ?i AND `status` = ?i AND `stage` =?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $change_phone_story_id, $status, $stage, 1);
		if (!isset($row["change_phone_story_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * метод для получения записей по идентификатору истории
	 *
	 * @return Struct_Db_PivotPhone_PhoneChangeViaSmsStory[]
	 * @throws \cs_UnpackHasFailed
	 * @throws \cs_RowIsEmpty
	 */
	public static function getByStory(string $change_phone_story_map):array {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$change_phone_story_id = Type_Pack_ChangePhoneStory::getId($change_phone_story_map);

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `change_phone_story_id` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $change_phone_story_id, 2);

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_rowToStruct($row);
		}

		return $output;
	}

	/**
	 * получаем историю за временной отрезок
	 *
	 * @return Struct_Db_PivotPhone_PhoneChangeViaSmsStory[]
	 */
	public static function getListByPeriod(int $date_start, int $date_end, int $limit):array {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// индекса нет, используется только в сервисных целях в редких случаях
		$query = "SELECT * FROM `?p` WHERE `created_at` >= ?i AND `created_at` <= ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $date_start, $date_end, $limit);

		// бежимся по полученным результатам и собираем объекты
		$output = [];
		foreach ($list as $row) {
			$output[] = self::_rowToStruct($row);
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotPhone_PhoneChangeViaSmsStory
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotPhone_PhoneChangeViaSmsStory {

		return new Struct_Db_PivotPhone_PhoneChangeViaSmsStory(
			$row["change_phone_story_id"],
			$row["phone_number"],
			$row["status"],
			$row["stage"],
			$row["resend_count"],
			$row["error_count"],
			$row["created_at"],
			$row["updated_at"],
			$row["next_resend_at"],
			$row["sms_id"],
			$row["sms_code_hash"],
		);
	}

	// получает таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
