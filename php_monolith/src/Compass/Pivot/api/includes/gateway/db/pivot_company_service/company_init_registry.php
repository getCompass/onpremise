<?php

namespace Compass\Pivot;

/**
 * Гейтвей для работы с таблицей БД pivot_company_service.company_init_registry
 */
class Gateway_Db_PivotCompanyService_CompanyInitRegistry extends Gateway_Db_PivotCompanyService_Main {

	protected const _TABLE_KEY = "company_init_registry";

	/**
	 * Получить одну запись из базы
	 *
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyInitRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $company_id):Struct_Db_PivotCompanyService_CompanyInitRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		$query = "SELECT * from `?p` WHERE `company_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $company_id, 1);

		if (!isset($row["company_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить одну запись из базы
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getVacantCount():int {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		$query = "SELECT count(*) as `count` from `?p` WHERE `is_vacant` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, 1, 1);

		return $row["count"] ?? 0;
	}

	/**
	 * Получить одну запись из базы
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyInitRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getVacantForUpdate():Struct_Db_PivotCompanyService_CompanyInitRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		$query = "SELECT * from `?p` WHERE `is_vacant` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, 1, 1);

		if (!isset($row["company_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("company not found in registry");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить количество вакантных компаний
	 *
	 * @return int
	 */
	public static function countVacant():int {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		$query  = "SELECT COUNT(*) count from `?p` WHERE `is_vacant` = ?i LIMIT ?i";
		$result = ShardingGateway::database($db_key)->getOne($query, $table_key, 1, 1);

		return (int) $result["count"];
	}

	/**
	 * Получить одну запись из базы
	 *
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyInitRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getForUpdate(int $company_id):Struct_Db_PivotCompanyService_CompanyInitRegistry {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
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
	 * @param Struct_Db_PivotCompanyService_CompanyInitRegistry $company_registry
	 *
	 * @return void
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotCompanyService_CompanyInitRegistry $company_registry):void {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		$insert_arr = [
			"company_id"             => $company_registry->company_id,
			"is_vacant"              => $company_registry->is_vacant,
			"is_deleted"             => $company_registry->is_deleted,
			"is_purged"              => $company_registry->is_purged,
			"creating_started_at"    => $company_registry->creating_started_at,
			"creating_finished_at"   => $company_registry->creating_finished_at,
			"became_vacant_at"       => $company_registry->became_vacant_at,
			"occupation_started_at"  => $company_registry->occupation_started_at,
			"occupation_finished_at" => $company_registry->occupation_finished_at,
			"deleted_at"             => $company_registry->deleted_at,
			"purged_at"              => $company_registry->purged_at,
			"created_at"             => $company_registry->created_at,
			"updated_at"             => $company_registry->updated_at,
			"occupant_user_id"       => $company_registry->occupant_user_id,
			"deleter_user_id"        => $company_registry->deleter_user_id,
			"logs"                   => $company_registry->logs,
			"extra"                  => $company_registry->extra,
		];
		ShardingGateway::database($db_key)->insert($table_key, $insert_arr);
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

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// добавляем метку времени updated_at
		$set["updated_at"] = time();

		// проверяем, что такие поля есть в таблице
		self::_checkFields($set);

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `company_id` = ?i LIMIT ?i";

		return ShardingGateway::database($db_key)->update($query, $table_key, $set, $company_id, 1);
	}

	/**
	 * Сформировать из записи таблицы объект
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyInitRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _formatRow(array $row):Struct_Db_PivotCompanyService_CompanyInitRegistry {

		// проверяем, что такие поля есть в таблице
		self::_checkFields($row);

		return new Struct_Db_PivotCompanyService_CompanyInitRegistry(
			(int) $row["company_id"],
			(bool) $row["is_vacant"],
			(bool) $row["is_deleted"],
			(bool) $row["is_purged"],
			(int) $row["creating_started_at"],
			(int) $row["creating_finished_at"],
			(int) $row["became_vacant_at"],
			(int) $row["occupation_started_at"],
			(int) $row["occupation_finished_at"],
			(int) $row["deleted_at"],
			(int) $row["purged_at"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(int) $row["occupant_user_id"],
			(int) $row["deleter_user_id"],
			fromJson($row["logs"]),
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

			if (!property_exists(Struct_Db_PivotCompanyService_CompanyInitRegistry::class, $field)) {
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