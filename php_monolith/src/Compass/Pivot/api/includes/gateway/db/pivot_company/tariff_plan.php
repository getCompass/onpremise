<?php

namespace Compass\Pivot;

/**
 * Класс-шлюз для работы с таблицей БД pivot_company_{N}m.tariff_plan_{N}.
 */
class Gateway_Db_PivotCompany_TariffPlan extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY       = "tariff_plan";
	protected const _PER_QUERY_LIMIT = 500;

	/**
	 * Вставляет новую запись в базу.
	 *
	 * @param int   $space_id
	 * @param array $data
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \queryException
	 */
	public static function insert(int $space_id, array $data):int {

		$shard_suffix = static::_getDbKey($space_id);
		$table_suffix = static::_getTableKey($space_id);

		$insert = [
			"space_id"         => $space_id,
			"type"             => $data["type"],
			"plan_id"          => $data["plan_id"],
			"valid_till"       => $data["valid_till"],
			"active_till"      => $data["active_till"],
			"free_active_till" => $data["free_active_till"],
			"created_at"       => $data["created_at"],
			"option_list"      => $data["option_list"],
			"payment_info"     => $data["payment_info"],
			"extra"            => $data["extra"],
		];

		return ShardingGateway::database($shard_suffix)->insert($table_suffix, $insert);
	}

	/**
	 * Возвращает все записи для указанного пространства.
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getBySpace(int $space_id, int $limit = self::_PER_QUERY_LIMIT):array {

		$shard_suffix = static::_getDbKey($space_id);
		$table_suffix = static::_getTableKey($space_id);

		// EXPLAIN INDEX company_id.valid_till
		$query  = "SELECT * FROM `?p` WHERE `space_id` = ?i ORDER BY `valid_till` DESC LIMIT ?i";
		$result = ShardingGateway::database($shard_suffix)->getAll($query, $table_suffix, $space_id, $limit);

		foreach ($result as $index => $item) {

			$result[$index]["option_list"]  = fromJson($item["option_list"]);
			$result[$index]["payment_info"] = fromJson($item["payment_info"]);
		}

		return $result;
	}

	/**
	 * Удаляет записи плана из таблицы для указанного пространства.
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function delete(int $space_id, int $type):void {

		assertNotPublicServer();

		$shard_suffix = static::_getDbKey($space_id);
		$table_suffix = static::_getTableKey($space_id);

		$query = "DELETE FROM `?p` WHERE `space_id` = ?i AND `type` = ?i LIMIT ?i";
		ShardingGateway::database($shard_suffix)->getAll($query, $table_suffix, $space_id, $type, static::_PER_QUERY_LIMIT);
	}

	/**
	 * Получает таблицу
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	protected static function _getTableKey(int $company_id):string {

		$shard = ceil($company_id / 1000000);

		$table_shard = self::_TABLE_KEY . "_$shard";
		self::_checkExistShard($table_shard, $company_id);

		return $table_shard;
	}
}