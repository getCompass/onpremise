<?php

namespace Compass\Pivot;

/**
 * Класс-шлюз для работы с таблицей БД pivot_company_{N}m.tariff_plan_history_{N}.
 */
class Gateway_Db_PivotCompany_TariffPlanHistory extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY = "tariff_plan_history";

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