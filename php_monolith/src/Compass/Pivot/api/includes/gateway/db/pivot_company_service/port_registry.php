<?php

namespace Compass\Pivot;

/**
 * Гейтвей для работы с таблицей БД pivot_company_service.port_registry
 */
class Gateway_Db_PivotCompanyService_PortRegistry extends Gateway_Db_PivotCompanyService_Main {

	protected const _TABLE_KEY = "port_registry";

	/**
	 * Получить одну запись из базы
	 *
	 * @param string $domino_id
	 * @param int    $port
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(string $domino_id, int $port):Struct_Db_PivotCompanyService_PortRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * from `?p` WHERE `port` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $port, 1);

		if (!isset($row["port"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("domino not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить все записи с портами которые с компанией
	 *
	 * @param string $domino_id
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getAllWithCompany(string $domino_id):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// запрос выбирает все записи, не нужен индекс
		$query = "SELECT * from `?p` WHERE `company_id` != ?i LIMIT ?i";
		$list  = ShardingGateway::database($db_key)->getAll($query, $table_key, 0, 999999999);

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Получить запись по id компании
	 *
	 * @param string $domino_id
	 * @param int    $company_id
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getActiveByCompanyId(string $domino_id, int $company_id):Struct_Db_PivotCompanyService_PortRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		$query = "SELECT * from `?p` WHERE `company_id` = ?i AND `status` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, Domain_Domino_Entity_Port_Registry::STATUS_ACTIVE, 1);

		if (!isset($row["port"])) {

			if (isTestServer()) {

				// пытаемся понять проблему, в каком состоянии находится компания, что мы ее не нашли (не закончила прогреваться или еще удаляется)
				$query = "SELECT * from `?p` WHERE `company_id` = ?i LIMIT ?i";

				$row = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, 1);
				$row = toJson($row);

				$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
				$company = toJson($company);

				Type_System_Admin::log("domino_not_found_exception", "не нашли компанию запись регистра {$row} для компании {$company}");
			}

			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("domino not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить запись по id компании
	 *
	 * @param string $domino_id
	 * @param int    $company_id
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getActiveByCompanyIdForUpdate(string $domino_id, int $company_id):Struct_Db_PivotCompanyService_PortRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		$query = "SELECT * from `?p` WHERE `company_id` = ?i AND `status` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, 10, 1);

		if (!isset($row["port"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("domino not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить одну запись из базы для обновления
	 *
	 * @param string $domino_id
	 * @param int    $port
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getForUpdate(string $domino_id, int $port):Struct_Db_PivotCompanyService_PortRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// индекс не нужен так как запрос для скрипта
		$query = "SELECT * from `?p` WHERE `port` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $port, 1);

		if (!isset($row["port"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("port not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить одну запись из базы без компании для обновления
	 *
	 * @param string $domino_id
	 * @param int    $type
	 * @param int    $status
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOneVacantCompanyForUpdate(string $domino_id, int $type, int $status):Struct_Db_PivotCompanyService_PortRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// индекс не нужен так как запрос для скрипта
		$query = "SELECT * from `?p` WHERE `company_id` = ?i AND `type` = ?i AND `status` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, 0, $type, $status, 1);

		if (!isset($row["port"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("free company not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить одну запись из базы без компании для обновления
	 *
	 * @param string $domino_id
	 * @param int    $type
	 * @param int    $status
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getByCriteriaForUpdate(string $domino_id, int $type, int $status):Struct_Db_PivotCompanyService_PortRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		$query = "SELECT * from `?p` WHERE `company_id` = ?i AND `type` = ?i AND `status` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, 0, $type, $status, 1);

		if (!isset($row["port"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("there is no port with given criteria: type {$type}, status {$status}");
		}

		return self::_formatRow($row);
	}

	/**
	 * Возвращает порт подходящего типа для резовлва.
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getForResolveForCompany(string $domino_id, int $type, int $status, int $company_id, int $locked_till):Struct_Db_PivotCompanyService_PortRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		$query = "SELECT * from `?p` WHERE `company_id` = ?i AND `type` = ?i AND `status` = ?i AND `locked_till` < ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, $type, $status, $locked_till, 1);

		if (!isset($row["port"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("there is no port with given criteria: type {$type}, status {$status}");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить свободный сервисный порт
	 *
	 * @param string $domino_id
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getServiceVoidPortForUpdate(string $domino_id):Struct_Db_PivotCompanyService_PortRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// запрос проверен на EXPLAIN(INDEX=get_by_status)
		$query = "SELECT * from `?p` WHERE `status` = ?i AND `type` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($db_key)->getOne(
			$query, $table_key, Domain_Domino_Entity_Port_Registry::STATUS_VOID, Domain_Domino_Entity_Port_Registry::TYPE_SERVICE, 1);

		if (!isset($row["port"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("service port not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * Возвращает все порты, которые находятся в статусе invalid.
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getForReset(string $domino_id, array $status_list):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// EXPLAIN Без explain — служебный метод
		$query  = "SELECT * from `?p` WHERE `status` IN (?a) LIMIT ?i";
		$result = ShardingGateway::database($db_key)->getAll($query, $table_key, $status_list, 10000);

		$output = [];

		foreach ($result as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Добавить запись в таблицу
	 *
	 * @param string                                     $domino_id
	 * @param Struct_Db_PivotCompanyService_PortRegistry $domino
	 *
	 * @return void
	 * @throws \queryException
	 */
	public static function insert(string $domino_id, Struct_Db_PivotCompanyService_PortRegistry $domino):void {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		$insert_arr = [
			"port"       => $domino->port,
			"status"     => $domino->status,
			"type"       => $domino->type,
			"created_at" => $domino->created_at,
			"updated_at" => $domino->updated_at,
			"company_id" => $domino->company_id,
			"extra"      => $domino->extra,
		];
		ShardingGateway::database($db_key)->insert($table_key, $insert_arr);
	}

	/**
	 * Обновить запись в базе
	 *
	 * @param string $domino_id
	 * @param int    $port
	 * @param array  $set
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $domino_id, int $port, array $set):int {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey($domino_id);

		// добавляем метку времени updated_at
		$set["updated_at"] = time();

		// проверяем, что такие поля есть в таблице
		self::_checkFields($set);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `port` = ?i LIMIT ?i";

		return ShardingGateway::database($db_key)->update($query, $table_key, $set, $port, 1);
	}

	/**
	 * Сформировать из записи таблицы объект
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _formatRow(array $row):Struct_Db_PivotCompanyService_PortRegistry {

		// проверяем, что такие поля есть в таблице
		self::_checkFields($row);

		return new Struct_Db_PivotCompanyService_PortRegistry(
			$row["port"],
			$row["status"],
			$row["type"],
			$row["locked_till"],
			$row["created_at"],
			$row["updated_at"],
			$row["company_id"],
			fromJson($row["extra"]),
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

			if (!property_exists(Struct_Db_PivotCompanyService_PortRegistry::class, $field)) {
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