<?php

namespace Compass\Migration;

/**
 * основной класс миграций
 */
class Type_Migration_Main {

	protected const _START_TIME                 = 1609484461; // Начало 2020-го года
	public const    DATABASE_SCHEMA_PATH        = PATH_SQL . "database_schema.yaml";
	public const    MIGRATION_TYPE_UP           = 1;
	public const    MIGRATION_TYPE_DOWN         = 1;
	public const    MIGRATION_TYPE_LEGACY_CLEAN = 2;

	public const MIGRATION_RELEASE_PATH      = "release";
	public const MIGRATION_LEGACY_CLEAN_PATH = "legacy_clean";

	// получаем путь где лежат миграции для базы
	public static function getMigrationPath(string $database_name, string $type = self::MIGRATION_RELEASE_PATH):string {

		$database_list_yaml = file_get_contents(Type_Migration_Main::DATABASE_SCHEMA_PATH);
		$database_list      = yaml_parse($database_list_yaml)["databaseList"];

		foreach ($database_list as $database) {

			if ($database_name == $database["name"]) {
				return PATH_SQL . "/{$type}/" . $database["migration_path"];
			}
		}

		throw new parseException("database schema not found in config");
	}

	// получаем путь где лежат миграции для базы
	public static function getDatabaseFromConfig(string $database_name):array {

		$database_list_yaml = file_get_contents(Type_Migration_Main::DATABASE_SCHEMA_PATH);
		$database_list      = yaml_parse($database_list_yaml)["databaseList"];

		foreach ($database_list as $database) {

			if ($database["name"] == $database_name) {
				return $database;
			}
		}
		throw new parseException("database not found");
	}

	// получаем путь где лежат миграции для базы
	public static function getFullDatabaseNameListFromConfig(array $database):array {

		if (!isset($database["sharding"])) {
			return [$database["name"]];
		}

		if (mb_strtolower($database["sharding"]) == "year") {

			$start_year = date("Y", self::_START_TIME);
			$last_year  = date("Y", time());

			// есди сейчас декабрь то пора делать базы еще и на новый год
			if (date("m", time()) == 12) {
				$last_year = intval(date("Y", time())) + 1;
			}

			$range = range($start_year, $last_year);

			$output = [];
			foreach ($range as $year) {
				$output[] = $database["name"] . "_" . $year;
			}
			return $output;
		}
		throw new parseException("unknown database sharding");
	}

	/**
	 * Получаем полные имена таблиц
	 *
	 * @param array $table
	 *
	 * @return array
	 * @throws parseException
	 */
	public static function getFullTableNameListFromConfig(array $table):array {

		if (!isset($table["sharding"])) {
			return [$table["name"]];
		}
		if (mb_strtolower($table["sharding"]) == "month") {

			$range = range(1, 12);

			$output = [];
			foreach ($range as $year) {
				$output[] = $table["name"] . "_" . $year;
			}
			return $output;
		}
		if (mb_strtolower($table["sharding"]) == "ceil_10") {

			$range = range(0, 9);

			$output = [];
			foreach ($range as $year) {
				$output[] = $table["name"] . "_" . $year;
			}
			return $output;
		}
		throw new parseException("unknown database sharding");
	}
}