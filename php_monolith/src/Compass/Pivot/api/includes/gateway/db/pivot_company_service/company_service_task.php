<?php

namespace Compass\Pivot;

/**
 * Гейтвей для работы с таблицей БД pivot_company_service.company_service_task
 */
class Gateway_Db_PivotCompanyService_CompanyServiceTask extends Gateway_Db_PivotCompanyService_Main {

	protected const _TABLE_KEY = "company_service_task";

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
	 * Взять таски для работы
	 *
	 * @param int $need_work
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getForWork(int $need_work, int $limit, int $offset):array {

		$output = [];

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`need_work`)
		$query  = "SELECT * from `?p` WHERE `is_failed` = ?i AND `need_work` < ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($db_key)->getAll($query, $table_key, 0, $need_work, $limit, $offset);

		foreach ($result as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Вставить запись в таблицу
	 *
	 * @param int   $task_type
	 * @param int   $need_work
	 * @param int   $company_id
	 * @param array $data
	 *
	 * @return void
	 * @throws \queryException
	 */
	public static function insert(int $task_type, int $need_work, int $company_id, array $data):int {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		$insert_arr = [
			"is_failed"   => 0,
			"need_work"   => $need_work,
			"type"        => $task_type,
			"started_at"  => 0,
			"finished_at" => 0,
			"created_at"  => time(),
			"updated_at"  => 0,
			"company_id"  => $company_id,
			"logs"        => "",
			"data"        => $data,
		];

		return ShardingGateway::database($db_key)->insert($table_key, $insert_arr);
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
	 * Обновляем список записей в базе
	 *
	 * @param array $task_id_list
	 * @param array $set
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function setList(array $task_id_list, array $set):int {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// добавляем метку времени updated_at
		$set["updated_at"] = time();

		// проверяем, что такие поля есть в таблице
		self::_checkFields($set);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `task_id` IN (?a) LIMIT ?i";

		return ShardingGateway::database($db_key)->update($query, $table_key, $set, $task_id_list, count($task_id_list));
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
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function setCompanyPurge(int $company_id):int {

		assertTestServer();

		// формируем и осуществляем запрос
		// индекс не требуется
		$query = "DELETE FROM `?p` WHERE company_id = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update(
			$query,
			self::_TABLE_KEY,
			$company_id,
			999999
		);
	}

	/**
	 * Получает один таск указанного типа для компании.
	 * Используется для бэкдора.
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyServiceTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOneSpecifiedByCompany(int $company_id, int $task_type):Struct_Db_PivotCompanyService_CompanyServiceTask {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * from `?p` WHERE `company_id` = ?i AND `type` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, $task_type, 1);

		if (!isset($row["task_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company service task not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получаем таски
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyServiceTask[]
	 */
	public static function getList(array $task_id_list):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * from `?p` WHERE `task_id` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_getTableKey(), $task_id_list, count($task_id_list));

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Получим все таски
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 */
	public static function getAll():array {

		assertTestServer();

		$output = [];

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос только для тестового сервера, индекс не требуется
		$query  = "SELECT * from `?p` WHERE TRUE LIMIT ?i";
		$result = ShardingGateway::database($db_key)->getAll($query, $table_key, 999999);

		foreach ($result as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Получение количества записей
	 *
	 * @return int
	 */
	public static function getTotalCount():int {

		// запрос проверен на EXPLAIN (INDEX=need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey(), 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param int $need_work
	 * @param int $is_failed
	 *
	 * @return int
	 */
	public static function getExpiredCount(int $is_failed, int $need_work):int {

		// запрос проверен на EXPLAIN (INDEX=need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `is_failed` = ?i AND `need_work` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey(), $is_failed, $need_work, 1);
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