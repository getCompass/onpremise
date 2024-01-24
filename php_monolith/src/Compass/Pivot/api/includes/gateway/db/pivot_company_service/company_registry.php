<?php

namespace Compass\Pivot;

/**
 * Гейтвей для работы с таблицей БД pivot_company_service.company_registry
 */
class Gateway_Db_PivotCompanyService_CompanyRegistry extends Gateway_Db_PivotCompanyService_Main {

	protected const _TABLE_KEY = "company_registry";

	/**
	 * Получить одну запись из базы
	 *
	 * @param string $domino_id
	 * @param int    $company_id
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(string $domino_id, int $company_id):Struct_Db_PivotCompanyService_CompanyRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * from `?p` WHERE `company_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, 1);

		if (!isset($row["company_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Возвращает список всех компаний на доминошке
	 */
	public static function getAllCompanyIdList(string $domino_id, int $limit = 99999999, int $offset = 0):array {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($domino_id);

		$query  = "SELECT `company_id` FROM `?p` WHERE TRUE LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($shard_key)->getAll($query, $table_name, $limit, $offset);

		// возвращаем массив идентификаторов
		return array_map(fn(array $row) => (int) $row["company_id"], $result);
	}

	/**
	 * Получаем активные компании для миграций
	 */
	public static function getActiveCompanyIdList(string $domino_id):array {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($domino_id);

		// EXPLAIN выполняется только для миграций
		$query  = "SELECT `company_id` FROM `?p` WHERE `is_hibernated` = ?i AND `is_mysql_alive` = ?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getAll($query, $table_name, 0, 1, 999999);

		// возвращаем массив идентификаторов
		return array_map(fn(array $row) => (int) $row["company_id"], $result);
	}

	/**
	 * получить список всех переданных компаний по id на доминошке
	 */
	public static function getByCompanyIdList(string $domino_id, array $company_id_list):array {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($domino_id);

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query  = "SELECT * FROM `?p` WHERE `company_id` IN (?a) LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getAll($query, $table_name, $company_id_list, count($company_id_list));

		$row_list = [];
		foreach ($result as $row) {
			$row_list[] = self::_formatRow($row);
		}

		return $row_list;
	}

	/**
	 * Получить одну запись из базы с блокировкой записи
	 *
	 * @param string $domino_id
	 * @param int    $company_id
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getForUpdate(string $domino_id, int $company_id):Struct_Db_PivotCompanyService_CompanyRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * from `?p` WHERE `company_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, 1);

		if (!isset($row["company_id"])) {

			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Добавить запись в таблицу
	 *
	 * @param string                                        $domino_id
	 * @param Struct_Db_PivotCompanyService_CompanyRegistry $company_registry
	 *
	 * @return void
	 */
	public static function insert(string $domino_id, Struct_Db_PivotCompanyService_CompanyRegistry $company_registry):void {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		$insert_arr = [
			"company_id"     => $company_registry->company_id,
			"is_busy"        => $company_registry->is_busy,
			"is_hibernated"  => $company_registry->is_hibernated,
			"is_mysql_alive" => $company_registry->is_mysql_alive,
			"created_at"     => $company_registry->created_at,
			"updated_at"     => $company_registry->updated_at,
		];

		ShardingGateway::database($db_key)->insert($table_key, $insert_arr);
	}

	/**
	 * Обновить запись в базе
	 *
	 * @param string $domino_id
	 * @param int    $company_id
	 * @param array  $set
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $domino_id, int $company_id, array $set):int {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// добавляем метку времени updated_at
		$set["updated_at"] = time();

		// проверяем, что такие поля есть в таблице
		self::_checkFields($set);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `company_id` = ?i LIMIT ?i";

		return ShardingGateway::database($db_key)->update($query, $table_key, $set, $company_id, 1);
	}

	/**
	 * Удаляет запись о компании из реестра.
	 */
	public static function delete(string $domino_id, int $company_id):void {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// EXPLAIN PRIMARY
		$query = "DELETE FROM `?p` WHERE `company_id` = ?i LIMIT ?i";
		ShardingGateway::database($db_key)->delete($query, $table_key, $company_id, 1);
	}

	/**
	 * Получаем компании по is_busy и is_hibernate
	 */
	public static function getAllByBusyHibernate(string $domino_id, int $is_busy, int $is_hibernate):array {

		// !!! выполнение только на тестовых и стейдже
		assertNotPublicServer();

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($domino_id);

		$query  = "SELECT * FROM `?p` WHERE `is_busy` = ?i AND `is_hibernated` = ?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getAll($query, $table_name, $is_busy, $is_hibernate, 999999);

		$row_list = [];
		foreach ($result as $row) {
			$row_list[] = self::_formatRow($row);
		}

		return $row_list;
	}

	/**
	 * Сформировать из записи таблицы объект
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _formatRow(array $row):Struct_Db_PivotCompanyService_CompanyRegistry {

		// проверяем, что такие поля есть в таблице
		self::_checkFields($row);

		return new Struct_Db_PivotCompanyService_CompanyRegistry(
			(int) $row["company_id"],
			(bool) $row["is_busy"],
			(bool) $row["is_hibernated"],
			(bool) $row["is_mysql_alive"],
			(int) $row["created_at"],
			(int) $row["updated_at"]
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

			if (!property_exists(Struct_Db_PivotCompanyService_CompanyRegistry::class, $field)) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("send unknown field");
			}
		}
	}

	/**
	 * Вернуть название таблицы
	 *
	 * @param string $domino_id
	 *
	 * @return string
	 */
	protected static function _getTableKey(string $domino_id):string {

		return static::_TABLE_KEY . "_" . $domino_id;
	}
}