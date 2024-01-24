<?php

namespace Compass\Pivot;

/**
 * Гейтвей для работы с таблицей БД pivot_company_{10m}.tariff_plan_task_history
 */
class Gateway_Db_PivotCompany_TariffPlanTaskHistory extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY = "tariff_plan_task_history";
	protected const _MAX_COUNT = 10000;

	/**
	 * Получить одну запись из базы
	 *
	 * @param int $company_id
	 * @param int $id
	 *
	 * @return Struct_Db_PivotCompany_TariffPlanTaskHistory
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $company_id, int $id):Struct_Db_PivotCompany_TariffPlanTaskHistory {

		$db_key    = self::_getDbKey($company_id);
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * from `?p` WHERE `id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $id, 1);

		if (!isset($row["space_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company service task not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * Вставить запись в таблицу
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanTaskHistory $tariff_plan_task_history
	 *
	 * @return int
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotCompany_TariffPlanTaskHistory $tariff_plan_task_history):int {

		$db_key    = self::_getDbKey($tariff_plan_task_history->space_id);
		$table_key = self::_getTableKey();

		$insert_arr = [
			"id"            => $tariff_plan_task_history->id,
			"space_id"      => $tariff_plan_task_history->space_id,
			"type"          => $tariff_plan_task_history->type,
			"status"        => $tariff_plan_task_history->status,
			"in_queue_time" => $tariff_plan_task_history->in_queue_time,
			"created_at"    => $tariff_plan_task_history->created_at,
			"logs"          => $tariff_plan_task_history->logs,
			"extra"         => $tariff_plan_task_history->extra,
		];

		return ShardingGateway::database($db_key)->insert($table_key, $insert_arr);
	}

	/**
	 * Получить историю задач
	 *
	 * @param string $sharding_key
	 * @param int    $status
	 * @param int    $start_time
	 * @param int    $end_time
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getHistory(string $sharding_key, int $status, int $start_time, int $end_time):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`status.created_at`)
		$query  = "SELECT * from `?p` WHERE `status` = ?i AND `created_at` BETWEEN ?i AND ?i ORDER BY `created_at` DESC LIMIT ?i";
		$result = ShardingGateway::database($sharding_key)->getAll($query, $table_key, $status, $start_time, $end_time, self::_MAX_COUNT);

		return array_map(fn(array $row) => self::_formatRow($row), $result);
	}

	public static function getAverageInQueueTime(string $sharding_key, int $start_time, int $end_time):int {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`status.created_at`)
		$query = "SELECT AVG(`in_queue_time`) AS `avg_queue_time` from `?p` 
                                                WHERE `status` IN (?a) AND `created_at` BETWEEN ?i AND ?i ORDER BY `created_at` DESC LIMIT ?i";
		$row   = ShardingGateway::database($sharding_key)->getOne(
			$query,
			$table_key,
			[Domain_Space_Entity_Tariff_PlanTask::TASK_STATUS_SUCCESS, Domain_Space_Entity_Tariff_PlanTask::TASK_STATUS_ERROR],
			$start_time,
			$end_time,
			self::_MAX_COUNT);

		return $row["avg_queue_time"] ?? 0;
	}

	/**
	 * Сформировать из записи таблицы объект
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotCompany_TariffPlanTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _formatRow(array $row):Struct_Db_PivotCompany_TariffPlanTaskHistory {

		// проверяем, что такие поля есть в таблице
		self::_checkFields($row);

		return new Struct_Db_PivotCompany_TariffPlanTaskHistory(
			(int) $row["id"],
			(int) $row["space_id"],
			(int) $row["type"],
			(int) $row["status"],
			(int) $row["in_queue_time"],
			(int) $row["created_at"],
			(string) $row["logs"],
			(array) $row["extra"],
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

			if (!property_exists(Struct_Db_PivotCompany_TariffPlanTaskHistory::class, $field)) {
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