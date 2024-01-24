<?php

namespace Compass\Pivot;

/**
 * Гейтвей для работы с таблицей БД pivot_company_{10m}.tariff_plan_observe
 */
class Gateway_Db_PivotCompany_TariffPlanObserve extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY = "tariff_plan_observe";

	/**
	 * Получить одну запись из базы
	 *
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotCompany_TariffPlanObserve
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $company_id):Struct_Db_PivotCompany_TariffPlanObserve {

		$db_key    = self::_getDbKey($company_id);
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * from `?p` WHERE `space_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, 1);

		if (!isset($row["space_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company service task not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * Взять таски для работы
	 *
	 * @param string $sharding_key
	 * @param int    $observe_at
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getForObserve(string $sharding_key, int $observe_at, int $limit, int $offset = 0):array {

		$output    = [];
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`observe_at`)
		$query  = "SELECT * from `?p` WHERE `observe_at` < ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($sharding_key)->getAll($query, $table_key, $observe_at, $limit, $offset);

		foreach ($result as $row) {
			$output[$row["space_id"]] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Вставить запись в таблицу
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe
	 *
	 * @return int
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe):int {

		$db_key    = self::_getDbKey($tariff_plan_observe->space_id);
		$table_key = self::_getTableKey();

		$insert_arr = [
			"space_id"        => $tariff_plan_observe->space_id,
			"observe_at"      => $tariff_plan_observe->observe_at,
			"report_after"    => $tariff_plan_observe->report_after,
			"last_error_logs" => $tariff_plan_observe->last_error_logs,
			"created_at"      => $tariff_plan_observe->created_at,
			"updated_at"      => $tariff_plan_observe->updated_at,
		];

		return ShardingGateway::database($db_key)->insert($table_key, $insert_arr);
	}

	/**
	 * Обновить запись в базе
	 *
	 * @param int   $company_id
	 * @param array $set
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(int $company_id, array $set):int {

		$db_key    = self::_getDbKey($company_id);
		$table_key = self::_getTableKey();

		// добавляем метку времени updated_at
		$set["updated_at"] = time();

		// проверяем, что такие поля есть в таблице
		self::_checkFields($set);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `space_id` = ?i LIMIT ?i";

		return ShardingGateway::database($db_key)->update($query, $table_key, $set, $company_id, 1);
	}

	/**
	 * Обновить записи в базе
	 *
	 * @param array $company_id_list
	 * @param array $set
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function setList(array $company_id_list, array $set):void {

		$grouped_by_shard_list = [];
		foreach ($company_id_list as $company_id) {

			$key                           = sprintf("%s.%s", self::_getDbKey($company_id), self::_getTableKey());
			$grouped_by_shard_list[$key][] = $company_id;
		}

		// добавляем метку времени updated_at
		$set["updated_at"] = time();

		// проверяем, что такие поля есть в таблице
		self::_checkFields($set);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `space_id` IN (?a) LIMIT ?i";

		// делаем запросы
		foreach ($grouped_by_shard_list as $key => $company_id_list) {

			// формируем и осуществляем запрос
			[$shard_key, $table_name] = explode(".", $key);
			ShardingGateway::database($shard_key)->update($query, $table_name, $set, $company_id_list, count($company_id_list));
		}
	}

	/**
	 * Удалить запись из базы
	 *
	 * @param int $company_id
	 *
	 * @return void
	 */
	public static function delete(int $company_id):void {

		$db_key    = self::_getDbKey($company_id);
		$table_key = self::_getTableKey();

		// удаляем запись
		// запрос проверен на EXPLAIN(INDEX=`PRIMARY`)
		$query = "DELETE FROM `?p` WHERE `space_id` = ?i LIMIT ?i";
		ShardingGateway::database($db_key)->delete($query, $table_key, $company_id, 1);
	}

	/**
	 * Удалить записи из базы
	 *
	 * @param array $company_id_list
	 *
	 * @return void
	 */
	public static function deleteList(array $company_id_list):void {

		$grouped_by_shard_list = [];
		foreach ($company_id_list as $company_id) {

			$key                           = sprintf("%s.%s", self::_getDbKey($company_id), self::_getTableKey());
			$grouped_by_shard_list[$key][] = $company_id;
		}
		// удаляем запись
		// запрос проверен на EXPLAIN(INDEX=`PRIMARY`)
		$query = "DELETE FROM `?p` WHERE `space_id` IN (?a) LIMIT ?i";

		// делаем запросы
		foreach ($grouped_by_shard_list as $key => $company_id_list) {

			// формируем и осуществляем запрос
			[$shard_key, $table_name] = explode(".", $key);
			ShardingGateway::database($shard_key)->delete($query, $table_name, $company_id_list, 1);
		}
	}

	/**
	 * @param string $sharding_key
	 * @param int    $report_after
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getByReportAfter(string $sharding_key, int $report_after, int $limit, int $offset = 0):array {

		$output    = [];
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`report_after`)
		$query  = "SELECT * from `?p` WHERE `report_after` BETWEEN ?i AND ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($sharding_key)->getAll($query, $table_key, 1, $report_after, $limit, $offset);

		foreach ($result as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Сформировать из записи таблицы объект
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotCompany_TariffPlanObserve
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _formatRow(array $row):Struct_Db_PivotCompany_TariffPlanObserve {

		// проверяем, что такие поля есть в таблице
		self::_checkFields($row);

		return new Struct_Db_PivotCompany_TariffPlanObserve(
			(int) $row["space_id"],
			(int) $row["observe_at"],
			(int) $row["report_after"],
			(string) $row["last_error_logs"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
		);
	}

	/**
	 * Проверить поля при выполнении запроса
	 *
	 * @param array $row
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _checkFields(array $row):void {

		// проверяем, что все переданные поля есть в записи
		foreach ($row as $field => $_) {

			if (!property_exists(Struct_Db_PivotCompany_TariffPlanObserve::class, $field)) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("send unknown field");
			}
		}
	}

	/**
	 * Вернуть название таблицы
	 *
	 * @return string
	 */
	protected static function _getTableKey():string {

		return static::_TABLE_KEY;
	}
}