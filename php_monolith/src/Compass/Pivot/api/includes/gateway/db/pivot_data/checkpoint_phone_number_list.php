<?php

namespace Compass\Pivot;

/**
 * Интерфейс для работы с БД списков номеров
 */
class Gateway_Db_PivotData_CheckpointPhoneNumberList extends Gateway_Db_PivotData_Main {

	protected const _TABLE_KEY = "checkpoint_phone_number_list";

	/**
	 * Получение записи из списка
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $list_type, string $phone_number_hash):Struct_Db_PivotData_CheckpointPhoneNumber {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `list_type` = ?i AND `phone_number_hash` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $list_type, $phone_number_hash, 1);

		if (!isset($row["phone_number_hash"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_PivotData_CheckpointPhoneNumber(
			$row["list_type"],
			$row["phone_number_hash"],
			$row["expires_at"],
		);
	}

	/**
	 * Установить запись
	 *
	 */
	public static function set(int $list_type, string $phone_number_hash, int $expires_at = 0):void {

		ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_TABLE_KEY, [
			"list_type"         => $list_type,
			"phone_number_hash" => $phone_number_hash,
			"expires_at"        => $expires_at,
		]);
	}

	/**
	 * Удаление записи из списка
	 *
	 */
	public static function delete(int $list_type, string $phone_number_hash):void {

		ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `list_type` = ?i AND `phone_number_hash` = ?s LIMIT ?i",
			self::_TABLE_KEY, $list_type, $phone_number_hash, 1);
	}
}