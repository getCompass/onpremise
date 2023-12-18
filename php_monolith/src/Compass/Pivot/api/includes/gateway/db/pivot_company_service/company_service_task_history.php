<?php

namespace Compass\Pivot;

/**
 * Гейтвей для работы с таблицей БД pivot_company_service.company_service_task_history
 */
class Gateway_Db_PivotCompanyService_CompanyServiceTaskHistory extends Gateway_Db_PivotCompanyService_Main {

	protected const _TABLE_KEY = "company_service_task_history";

	/**
	 * Получить одну запись из базы
	 *
	 * @param int $task_id
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyServiceTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $task_id):Struct_Db_PivotCompanyService_CompanyServiceTask {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * from `?p` WHERE `task_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $task_id, 1);

		if (!isset($row["task_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company service task not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить одну запись из базы по id компании и типу
	 *
	 * @param int $company_id
	 * @param int $type
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyServiceTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \parseException
	 */
	public static function getByCompanyIdAndType(int $company_id, int $type):Struct_Db_PivotCompanyService_CompanyServiceTask {

		assertTestServer();
		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос только для тестового сервера, индекс не требуется
		$query = "SELECT * from `?p` WHERE `company_id` = ?i AND `type` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, $type, 1);

		if (!isset($row["task_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company service task not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * Вставить запись в таблицу
	 *
	 * @param Struct_Db_PivotCompanyService_CompanyServiceTask $company_service_task
	 *
	 * @return void
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotCompanyService_CompanyServiceTask $company_service_task):void {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		$insert_arr = [
			"task_id"     => $company_service_task->task_id,
			"is_failed"   => $company_service_task->is_failed,
			"need_work"   => $company_service_task->need_work,
			"type"        => $company_service_task->type,
			"started_at"  => $company_service_task->started_at,
			"finished_at" => $company_service_task->finished_at,
			"created_at"  => $company_service_task->created_at,
			"updated_at"  => $company_service_task->updated_at,
			"company_id"  => $company_service_task->company_id,
			"logs"        => $company_service_task->logs,
			"data"        => $company_service_task->data,
		];

		ShardingGateway::database($db_key)->insert($table_key, $insert_arr);
	}

	/**
	 * Обновить запись в базе
	 *
	 * @param int   $task_id
	 * @param array $set
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(int $task_id, array $set):int {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// добавляем метку времени updated_at
		$set["updated_at"] = time();

		// проверяем, что такие поля есть в таблице
		self::_checkFields($set);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `task_id` = ?i LIMIT ?i";

		return ShardingGateway::database($db_key)->update($query, $table_key, $set, $task_id, 1);
	}

	/**
	 * Удалить запись из базы
	 *
	 * @param int $task_id
	 *
	 * @return void
	 */
	public static function delete(int $task_id):void {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// удаляем запись
		$query = "DELETE FROM `?p` WHERE `task_id` = ?i LIMIT ?i";
		ShardingGateway::database($db_key)->delete($query, $table_key, $task_id, 1);
	}

	/**
	 * Получение количества записей
	 *
	 * @param int $created_at
	 *
	 * @return int
	 */
	public static function getHistoryCount(int $created_at):int {

		// запрос проверен на EXPLAIN (INDEX=created_at)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `created_at` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey(), $created_at, 1);
		return $row["count"];
	}

	/**
	 * Сформировать из записи таблицы объект
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyServiceTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _formatRow(array $row):Struct_Db_PivotCompanyService_CompanyServiceTask {

		// проверяем, что такие поля есть в таблице
		self::_checkFields($row);

		if (isset($row["data"])) {
			$row["data"] = fromJson($row["data"]);
		}

		return new Struct_Db_PivotCompanyService_CompanyServiceTask(
			(int) $row["task_id"],
			(bool) $row["is_failed"],
			(int) $row["need_work"],
			(int) $row["type"],
			(int) $row["started_at"],
			(int) $row["finished_at"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(int) $row["company_id"],
			(string) $row["logs"],
			(array) $row["data"],
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

			if (!property_exists(Struct_Db_PivotCompanyService_CompanyServiceTask::class, $field)) {
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