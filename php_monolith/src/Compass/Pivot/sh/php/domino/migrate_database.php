<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

/**
 * Миграции для таблиц домино.
 */
class DominoDatabaseMigrator {

	/**
	 * Возвращает все таблицы портов домино.
	 */
	protected static function _fetchDominoPortRegistryTables():array {

		$table_list  = [];
		$table_names = [];

		$query      = "SHOW TABLES LIKE 'port\_registry\_%'";
		$raw_tables = ShardingGateway::database("pivot_company_service")
			->query($query)
			->fetchAll();

		foreach ($raw_tables as $raw_table) {
			$table_names[] = reset($raw_table);
		}

		foreach ($table_names as $table_name) {

			$columns = [];

			$query       = "SHOW COLUMNS FROM `$table_name`;";
			$raw_columns = ShardingGateway::database("pivot_company_service")
				->query($query)
				->fetchAll();

			foreach ($raw_columns as $raw_column) {
				$columns[$raw_column["Field"]] = true;
			}

			$table_list[$table_name] = ["columns" => $columns];
		}

		return $table_list;
	}

	/**
	 * Проверяет наличие указанной колонки в переданных данных таблицы.
	 */
	protected static function _hasTableColumn(array $table, string $column_name):bool {

		return isset($table["columns"][$column_name]);
	}

	/**
	 * Выполняет миграцию базы данных портов домино.
	 * Добавляет поле host для портов домино.
	 */
	protected static function _migrateUpAddHost():void {

		$to_migrate = [];
		$tables     = static::_fetchDominoPortRegistryTables();

		foreach ($tables as $table_name => $table_data) {

			if (!static::_hasTableColumn($table_data, "host")) {
				$to_migrate[] = $table_name;
			}
		}

		if (count($to_migrate) === 0) {
			return;
		}

		console("want to migrate up 'add host' on " . implode(", ", $to_migrate));

		$migrate_query_1 = "ALTER TABLE `::table_name` ADD COLUMN `host` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'кастомный домен, на котором доступен порт' AFTER `port`";
		$migrate_query_2 = "ALTER TABLE `::table_name` ADD INDEX `uniq` (`port`, `host`)";

		foreach ($to_migrate as $table_name) {

			$str_1 = str_replace("::table_name", $table_name, $migrate_query_1);
			$str_2 = str_replace("::table_name", $table_name, $migrate_query_2);

			ShardingGateway::database("pivot_company_service")->query("$str_1;$str_2");
		}

		console("migrate up 'add host' on " . implode(", ", $to_migrate) . " completed");
	}

	/**
	 * Запускает миграции для баз данных домино.
	 */
	public static function migrateUp():void {

		console("begin domino tables migration...");
		static::_migrateUpAddHost();
		console("begin domino tables migration done!");
	}
}

DominoDatabaseMigrator::migrateUp();