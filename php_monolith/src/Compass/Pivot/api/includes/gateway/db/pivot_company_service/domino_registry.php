<?php

namespace Compass\Pivot;

/**
 * Гейтвей для работы с таблицей БД pivot_company_service.domino_registry
 */
class Gateway_Db_PivotCompanyService_DominoRegistry extends Gateway_Db_PivotCompanyService_Main {

	protected const _TABLE_KEY = "domino_registry";

	/**
	 * Получить одну запись из базы
	 *
	 * @param string $domino_id
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(string $domino_id):Struct_Db_PivotCompanyService_DominoRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * from `?p` WHERE `domino_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $domino_id, 1);

		if (!isset($row["domino_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("domino not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить одну запись из базы для создания
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOneForCreate():Struct_Db_PivotCompanyService_DominoRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * from `?p` WHERE `is_company_creating_allowed` = ?i AND `common_active_port_count` < `common_port_count` LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, 1, 1);

		if (!isset($row["domino_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("domino not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Возвращает одно домино, пригодное для релокации с указанного.
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOneForRelocateByTier(string $domino_id, int $tier):Struct_Db_PivotCompanyService_DominoRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * from `?p` WHERE `tier` = ?i AND `domino_id` != ?s AND `common_active_port_count` < `common_port_count` LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $tier, $domino_id, 1);

		if (!isset($row["domino_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("domino not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Возвращает одно домино, пригодное для релокации с указанного.
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOneForRelocate(string $domino_id):Struct_Db_PivotCompanyService_DominoRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// EXPLAIN PRIMARY
		$query = "SELECT * from `?p` WHERE `domino_id` != ?s AND `common_active_port_count` < `common_port_count` LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $domino_id, 1);

		if (!isset($row["domino_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("domino not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить все записи из базы
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getAll():array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос для получения всех записей, индекс не надо
		$query = "SELECT * from `?p` WHERE TRUE LIMIT ?i";
		$list  = ShardingGateway::database($db_key)->getAll($query, $table_key, 9999999);

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Получить одну запись из базы по mysql_host
	 *
	 * @param string $database_host
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOneByDatabaseHost(string $database_host):Struct_Db_PivotCompanyService_DominoRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// индекса нет, запрос не используется в основной логике
		$query = "SELECT * from `?p` WHERE `database_host` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $database_host, 1);

		if (!isset($row["domino_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("domino not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить одну запись из базы по code_host
	 *
	 * @param string $code_host
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOneByCodeHost(string $code_host):Struct_Db_PivotCompanyService_DominoRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// индекса нет, запрос не используется в основной логике
		$query = "SELECT * from `?p` WHERE `code_host` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $code_host, 1);

		if (!isset($row["domino_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("domino not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Добавить запись в таблицу
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino
	 *
	 * @return void
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotCompanyService_DominoRegistry $domino):void {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		$insert_arr = [
			"domino_id"                   => $domino->domino_id,
			"code_host"                   => $domino->code_host,
			"database_host"               => $domino->database_host,
			"created_at"                  => $domino->created_at,
			"updated_at"                  => $domino->updated_at,
			"is_company_creating_allowed" => $domino->is_company_creating_allowed,
			"hibernation_locked_until"    => $domino->hibernation_locked_until,
			"tier"                        => $domino->tier,
			"common_port_count"           => $domino->common_port_count,
			"service_port_count"          => $domino->service_port_count,
			"reserved_port_count"         => $domino->reserved_port_count,
			"common_active_port_count"    => $domino->common_active_port_count,
			"service_active_port_count"   => $domino->service_active_port_count,
			"reserve_active_port_count"   => $domino->reserve_active_port_count,
			"extra"                       => $domino->extra,
		];
		ShardingGateway::database($db_key)->insert($table_key, $insert_arr);
	}

	/**
	 * Обновить запись в базе
	 *
	 * @param string $domino_id
	 * @param array  $set
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $domino_id, array $set):int {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// добавляем метку времени updated_at
		$set["updated_at"] = time();

		// проверяем, что такие поля есть в таблице
		self::_checkFields($set);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `domino_id` = ?s LIMIT ?i";

		return ShardingGateway::database($db_key)->update($query, $table_key, $set, $domino_id, 1);
	}

	/**
	 * Сформировать из записи таблицы объект
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _formatRow(array $row):Struct_Db_PivotCompanyService_DominoRegistry {

		// проверяем, что такие поля есть в таблице
		self::_checkFields($row);

		return new Struct_Db_PivotCompanyService_DominoRegistry(
			$row["domino_id"],
			$row["code_host"],
			$row["database_host"],
			$row["is_company_creating_allowed"],
			$row["hibernation_locked_until"],
			$row["tier"],
			$row["common_port_count"],
			$row["service_port_count"],
			$row["reserved_port_count"],
			$row["common_active_port_count"],
			$row["reserve_active_port_count"],
			$row["service_active_port_count"],
			$row["created_at"],
			$row["updated_at"],
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

			if (!property_exists(Struct_Db_PivotCompanyService_DominoRegistry::class, $field)) {
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