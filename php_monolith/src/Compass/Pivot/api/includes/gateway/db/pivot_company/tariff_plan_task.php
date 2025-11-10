<?php

namespace Compass\Pivot;

/**
 * Гейтвей для работы с таблицей БД pivot_company_{10m}.tariff_plan_task
 */
class Gateway_Db_PivotCompany_TariffPlanTask extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY = "tariff_plan_task";

	/**
	 * Получить одну запись из базы
	 *
	 * @param int $company_id
	 * @param int $id
	 *
	 * @return Struct_Db_PivotCompany_TariffPlanTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $company_id, int $id):Struct_Db_PivotCompany_TariffPlanTask {

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
	 * Взять таски для работы
	 *
	 * @param string $sharding_key
	 * @param int    $need_work
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return Struct_Db_PivotCompany_TariffPlanTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getForWork(string $sharding_key, int $need_work, int $limit, int $offset = 0):array {

		$output    = [];
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`status.need_work`)
		$query  = "SELECT * from `?p` WHERE `status` = ?i AND `need_work` < ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($sharding_key)->getAll($query, $table_key, Domain_Space_Entity_Tariff_PlanTask::TASK_STATUS_SUCCESS, $need_work, $limit, $offset);

		foreach ($result as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Взять таски по статусу
	 *
	 * @param string $sharding_key
	 * @param int    $status
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getByStatus(string $sharding_key, int $status, int $limit, int $offset = 0):array {

		$output    = [];
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`status.need_work`)
		$query  = "SELECT * from `?p` WHERE `status` = ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($sharding_key)->getAll($query, $table_key, $status, $limit, $offset);

		foreach ($result as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Получить одну запись из базы по типу
	 *
	 * @param int $company_id
	 * @param int $type
	 *
	 * @return Struct_Db_PivotCompany_TariffPlanTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \parseException
	 */
	public static function getByType(int $company_id, int $type):Struct_Db_PivotCompany_TariffPlanTask {

		// только для тестового сервера, здесь нет индекса!!!
		assertTestServer();

		$db_key    = self::_getDbKey($company_id);
		$table_key = self::_getTableKey();

		// нет индекса, используется только для бекдура
		$query = "SELECT * from `?p` WHERE `space_id` = ?i AND `status` = ?i AND `type` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, Domain_Space_Entity_Tariff_PlanTask::TASK_STATUS_SUCCESS, $type, 1);

		if (!isset($row["space_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company service task not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * Вставить запись в таблицу
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task
	 *
	 * @return int
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task):int {

		$db_key    = self::_getDbKey($tariff_plan_task->space_id);
		$table_key = self::_getTableKey();

		$insert_arr = [
			"space_id"   => $tariff_plan_task->space_id,
			"type"       => $tariff_plan_task->type,
			"status"     => $tariff_plan_task->status,
			"need_work"  => $tariff_plan_task->need_work,
			"created_at" => $tariff_plan_task->created_at,
			"updated_at" => $tariff_plan_task->updated_at,
			"logs"       => $tariff_plan_task->logs,
			"extra"      => $tariff_plan_task->extra,
		];

		return ShardingGateway::database($db_key)->insert($table_key, $insert_arr);
	}

	/**
	 * Обновить запись в базе
	 *
	 * @param int   $company_id
	 * @param int   $id
	 * @param array $set
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(int $company_id, int $id, array $set):int {

		$db_key    = self::_getDbKey($company_id);
		$table_key = self::_getTableKey();

		// добавляем метку времени updated_at
		$set["updated_at"] = time();

		// проверяем, что такие поля есть в таблице
		self::_checkFields($set);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `id` = ?i LIMIT ?i";

		return ShardingGateway::database($db_key)->update($query, $table_key, $set, $id, 1);
	}

	/**
	 * Удалить запись из базы
	 *
	 * @param int $company_id
	 * @param int $id
	 *
	 * @return void
	 */
	public static function delete(int $company_id, int $id):void {

		$db_key    = self::_getDbKey($company_id);
		$table_key = self::_getTableKey();

		// удаляем запись
		// запрос проверен на EXPLAIN(INDEX=`PRIMARY`)
		$query = "DELETE FROM `?p` WHERE `id` = ?i LIMIT ?i";
		ShardingGateway::database($db_key)->delete($query, $table_key, $id, 1);
	}

	/**
	 * Сформировать из записи таблицы объект
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotCompany_TariffPlanTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _formatRow(array $row):Struct_Db_PivotCompany_TariffPlanTask {

		// проверяем, что такие поля есть в таблице
		self::_checkFields($row);

		return new Struct_Db_PivotCompany_TariffPlanTask(
			(int) $row["id"],
			(int) $row["space_id"],
			(int) $row["type"],
			(int) $row["status"],
			(int) $row["need_work"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
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

			if (!property_exists(Struct_Db_PivotCompany_TariffPlanTask::class, $field)) {
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