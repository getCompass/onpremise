<?php

namespace Compass\Migration;

use JetBrains\PhpStorm\Pure;

/**
 * Интерфейс для работы с таблицу company_system.migration_release_database_list
 */
class Gateway_Db_CompanySystem_MigrationReleaseDatabaseList extends Gateway_Db_CompanySystem_Main {

	protected const _TABLE_KEY = "migration_release_database_list";

	/**
	 * метод вставки записи в базу
	 *
	 * @param string $full_database_name
	 * @param string $database_name
	 * @param int    $is_completed
	 * @param int    $current_version
	 * @param int    $previous_version
	 * @param int    $expected_version
	 * @param int    $highest_version
	 * @param int    $last_migration_type
	 * @param int    $last_migration_at
	 * @param string $last_migration_file
	 *
	 * @return Struct_Db_CompanySystem_MigrationDatabase
	 * @throws queryException
	 * @long
	 */
	public static function insert(
		string $full_database_name,
		string $database_name,
		int    $is_completed,
		int    $current_version,
		int    $previous_version,
		int    $expected_version,
		int    $highest_version,
		int    $last_migration_type,
		int    $last_migration_at,
		string $last_migration_file,
	):Struct_Db_CompanySystem_MigrationDatabase {

		$insert = [
			"full_database_name" => $full_database_name,
			"database_name"      => $database_name,
			"is_completed"       => $is_completed,
			"current_version"    => $current_version,
			"previous_version"   => $previous_version,
			"expected_version"   => $expected_version,
			"highest_version"    => $highest_version,
			"last_migrated_type" => $last_migration_type,
			"last_migrated_at"   => $last_migration_at,
			"last_migrated_file" => $last_migration_file,
			"created_at"         => time(),
		];

		// осуществляем запрос
		customSharding::pdo(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);

		return self::_rowToObject($insert);
	}

	/**
	 * Получение значения из БД
	 *
	 * @param string $full_database_name
	 *
	 * @return Struct_Db_CompanySystem_MigrationDatabase
	 * @throws cs_RowIsEmpty
	 */
	public static function get(string $full_database_name):Struct_Db_CompanySystem_MigrationDatabase {

		$query = "SELECT * FROM `?p` WHERE `full_database_name` = ?s LIMIT ?i";
		$row   = customSharding::pdo(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $full_database_name, 1);

		if (!isset($row["full_database_name"])) {
			throw new cs_RowIsEmpty();
		}
		return self::_rowToObject($row);
	}

	/**
	 * Получение всех значений из БД
	 *
	 * @return Struct_Db_CompanySystem_MigrationDatabase[]
	 */
	public static function getAll():array {

		$query = "SELECT * FROM `?p` WHERE TRUE LIMIT ?i";
		$list  = customSharding::pdo(self::_getDbKey())->getAll($query, self::_TABLE_KEY, 9999999);

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_rowToObject($row);
		}

		return $output;
	}

	/**
	 * Установить атрибут
	 *
	 * @param string $full_database_name
	 * @param array  $set
	 *
	 * @throws parseException
	 */
	public static function set(string $full_database_name, array $set):void {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanySystem_MigrationDatabase::class, $field)) {
				throw new parseException("send unknown field");
			}
		}

		$query = "UPDATE `?p` SET ?u WHERE `full_database_name` = ?s LIMIT ?i";
		customSharding::pdo(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $full_database_name, 1);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_CompanySystem_MigrationDatabase
	 */
	#[Pure]
	protected static function _rowToObject(array $row):Struct_Db_CompanySystem_MigrationDatabase {

		return new Struct_Db_CompanySystem_MigrationDatabase(
			$row["full_database_name"],
			$row["database_name"],
			$row["is_completed"],
			$row["current_version"],
			$row["previous_version"],
			$row["expected_version"],
			$row["highest_version"],
			$row["last_migrated_type"],
			$row["last_migrated_at"],
			$row["last_migrated_file"],
			$row["created_at"],
		);
	}
}
