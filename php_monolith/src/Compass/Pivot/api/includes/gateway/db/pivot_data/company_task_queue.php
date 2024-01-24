<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы pivot_data.company_task_queue
 */
class Gateway_Db_PivotData_CompanyTaskQueue extends Gateway_Db_PivotData_Main {

	public const MAX_COUNT = 10000;

	protected const _TABLE_KEY = "company_task_queue";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(int $company_id, int $type, int $status, int $iteration_count, int $error_count, array $extra):Struct_Db_PivotData_CompanyTaskQueue {

		$insert_row = [
			"company_id"      => $company_id,
			"type"            => $type,
			"status"          => $status,
			"need_work"       => time(),
			"iteration_count" => $iteration_count,
			"error_count"     => $error_count,
			"created_at"      => time(),
			"updated_at"      => time(),
			"done_at"         => 0,
			"extra"           => $extra,
		];

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert_row);

		// отдаем структуру заявки
		return self::_rowToObject($insert_row);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $company_task_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotData_CompanyTaskQueue::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE company_task_id = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $company_task_id, 1);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function setCompanyPurge(int $company_id):int {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`company_id`)
		$query = "UPDATE `?p` SET status = ?i WHERE company_id = ?i AND status <> ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update(
			$query,
			self::_TABLE_KEY,
			Domain_Company_Entity_CronCompanyTask::STATUS_CANCELED,
			$company_id,
			Domain_Company_Entity_CronCompanyTask::STATUS_DONE,
			self::MAX_COUNT
		);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $company_task_id):Struct_Db_PivotData_CompanyTaskQueue {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `company_task_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $company_task_id, 1);

		if (!isset($row["company_task_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row["extra"] = fromJson($row["extra"]);
		return self::_rowToObject($row);
	}

	/**
	 * метод для получения тасков на исполнение
	 *
	 * @return array<Struct_Db_PivotData_CompanyTaskQueue>
	 */
	public static function getTasksNeedComplete(int $type, array $status_list):array {

		// запрос проверен на EXPLAIN (INDEX=`type_status_need_work`)
		$query = "SELECT * FROM `?p` WHERE type = ?i AND `status` IN (?a) AND `need_work` <= ?i LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $type, $status_list, time(), self::MAX_COUNT);

		$result = [];

		foreach ($rows as $row) {

			$row["extra"] = fromJson($row["extra"]);
			$result[]     = self::_rowToObject($row);
		}

		return $result;
	}

	/**
	 * Метод для удаления задачи
	 *
	 */
	public static function deleteTask(int $company_task_id):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `company_task_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->delete($query, self::_TABLE_KEY, $company_task_id, 1);
	}

	/**
	 * Получение количества записей
	 *
	 * @return int
	 */
	public static function getTotalCount():int {

		// запрос проверен на EXPLAIN (INDEX=type_status_need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param int $status
	 * @param int $need_work
	 *
	 * @return int
	 */
	public static function getExpiredCount(int $status, int $need_work):int {

		// запрос проверен на EXPLAIN (INDEX=type_status_need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `status` = ?i AND `need_work` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $status, $need_work, 1);
		return $row["count"];
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 *
	 */
	protected static function _rowToObject(array $row):Struct_Db_PivotData_CompanyTaskQueue {

		return new Struct_Db_PivotData_CompanyTaskQueue(
			$row["company_task_id"] ?? 0,
			$row["company_id"],
			$row["type"],
			$row["status"],
			$row["need_work"],
			$row["iteration_count"],
			$row["error_count"],
			$row["created_at"],
			$row["updated_at"],
			$row["done_at"],
			$row["extra"]
		);
	}
}