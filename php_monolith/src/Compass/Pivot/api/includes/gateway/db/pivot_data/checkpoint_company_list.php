<?php

namespace Compass\Pivot;

/**
 * Интерфейс для работы с БД списков компаний
 */
class Gateway_Db_PivotData_CheckpointCompanyList extends Gateway_Db_PivotData_Main {

	protected const _TABLE_KEY = "checkpoint_company_list";

	/**
	 * Получение записи из списка
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $list_type, int $company_id):Struct_Db_PivotData_CheckpointCompany {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `list_type` = ?i AND `company_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $list_type, $company_id, 1);

		if (!isset($row["company_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_PivotData_CheckpointCompany(
			$row["list_type"],
			$row["company_id"],
			$row["expires_at"],
		);
	}

	/**
	 * Установить запись
	 *
	 */
	public static function set(int $list_type, int $company_id, int $expires_at = 0):void {

		ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_TABLE_KEY, [
			"list_type"  => $list_type,
			"company_id" => $company_id,
			"expires_at" => $expires_at,
		]);
	}

	/**
	 * Удаление записи из списка
	 *
	 */
	public static function delete(int $list_type, int $company_id):void {

		ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `list_type` = ?i AND `company_id` = ?i LIMIT ?i",
			self::_TABLE_KEY, $list_type, $company_id, 1);
	}
}