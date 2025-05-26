<?php

namespace Compass\Migration;

/**
 * класс для проверки миграций
 */
class Type_Migration_Check {

	/**
	 * метод для старта проверки миграций
	 *
	 * @long
	 */
	public static function do(int $version, string $database_name, string $migration_path, string|false $full_database_name = false):void {

		$database = Type_Migration_Main::getDatabaseFromConfig($database_name);

		$full_database_name_list = Type_Migration_Main::getFullDatabaseNameListFromConfig($database);
		if ($full_database_name !== false) {
			$full_database_name_list = [$full_database_name];
		}

		$migration_yaml_path = self::_getMigrationYamlFilePath($version, $migration_path);

		// проверяем что структура базы даннных соответсвует ожидаемой
		$expected_db_struct = yaml_parse(file_get_contents($migration_yaml_path));

		foreach ($full_database_name_list as $full_database_name) {
			self::_assertDatabase($expected_db_struct, $full_database_name);
		}
	}

	/**
	 * Получаем пути для миграций
	 *
	 * @param int    $expected_version
	 * @param string $migration_path
	 *
	 * @return string
	 * @throws parseException
	 */
	protected static function _getMigrationYamlFilePath(int $expected_version, string $migration_path, string $type_path = Type_Migration_Main::MIGRATION_RELEASE_PATH):string {

		if (!is_dir($migration_path)) {
			throw new parseException("migrate file not found for database");
		}

		$migration_file_scan = scandir($migration_path);
		$migration_file_list = array_diff($migration_file_scan, ["..", "."]);

		$migration_yaml_path = "";

		foreach ($migration_file_list as $migration_file) {

			$migration_explode_list = explode("_", $migration_file);
			if (!isset($migration_explode_list[0]) || (int) $migration_explode_list[0] != $expected_version) {
				continue;
			}

			// нашли файл с нужной нам версией.
			if (str_ends_with($migration_file, ".yaml")) {

				$migration_yaml_path = $migration_path . "/" . $migration_file;
			}
		}

		if (mb_strlen($migration_yaml_path) < 1) {
			throw new parseException("migrate file not found");
		}
		return $migration_yaml_path;
	}

	/**
	 * проверяем базу данных
	 *
	 * @param array  $expected_db_struct
	 * @param string $full_database_name
	 *
	 * @throws parseException
	 */
	protected static function _assertDatabase(array $expected_db_struct, string $full_database_name):void {

		$expected_table_list = $expected_db_struct["tableList"];

		foreach ($expected_table_list as $expected_table) {

			$full_table_name_list = Type_Migration_Main::getFullTableNameListFromConfig($expected_table);
			foreach ($full_table_name_list as $full_table_name) {
				self::_assertTable($full_table_name, $expected_table, $full_database_name);
			}
		}
	}

	/**
	 * проверяем таблицу
	 *
	 * @param string $full_table_name
	 * @param array  $expected_table
	 * @param string $full_database_name
	 *
	 * @throws parseException
	 * @long
	 */
	protected static function _assertTable(string $full_table_name, array $expected_table, string $full_database_name):void {

		$table_schema = customSharding::pdo("information_schema")
			->query("SELECT * FROM tables where `TABLE_SCHEMA` = '$full_database_name' AND `TABLE_NAME` = '$full_table_name'")->fetch();
		if (!self::_isStringEquals($table_schema["ENGINE"], $expected_table["engine"])) {
			throw new parseException("engine not equals");
		}
		if (!inHtml($table_schema["TABLE_COLLATION"], $expected_table["charset"])) {
			throw new parseException("unknown charset");
		}

		$field_list = customSharding::pdo($full_database_name)->query("SHOW COLUMNS FROM `$full_table_name`;")->fetchAll();
		if (count($expected_table["fieldList"]) != count($field_list)) {
			throw new parseException("count field not equals");
		}

		self::_assertSortFields($field_list, $expected_table["fieldList"]);
		foreach ($field_list as $field) {
			self::_assertField($field, $expected_table);
		}

		$index_list = self::_getTableIndexList($full_database_name, $full_table_name);

		if (count($expected_table["indexList"]) != count($index_list)) {
			throw new parseException("count index not equals");
		}

		foreach ($index_list as $index_name => $index) {

			$expected_index_list = array_change_key_case($expected_table["indexList"]);
			if (!isset($expected_index_list[$index_name])) {
				throw new parseException("index not found");
			}

			array_walk($expected_index_list[$index_name], function(array $expected_index_field) use ($full_table_name, $index) {

				if (isset($expected_index_field["fields"])) {

					$checked_index  = implode(",", $index["fields"]);
					$expected_index = implode(",", $expected_index_field["fields"]);
					if (!self::_isStringEquals($checked_index, $expected_index)) {
						throw new parseException("column index not equals");
					}
				}
			});

			// проверяем уникальность
			if ($index["uniq"] == 1) {

				foreach ($expected_index_list[$index_name] as $value) {

					if (isset($value["uniq"]) && $value["uniq"] == 1) {
						return;
					}
				}
				throw new parseException("index not uniq");
			}
		}
	}

	/**
	 * Сверяем что сортировка полей правильная
	 *
	 * @param array $field_list
	 * @param array $expected_field_list
	 *
	 * @throws parseException
	 */
	protected static function _assertSortFields(array $field_list, array $expected_field_list):void {

		$i = 0;
		foreach ($expected_field_list as $expected_name => $excepted_field) {

			if ($field_list[$i]["Field"] != $expected_name) {
				throw new parseException("invalid field sort");
			}
			$i++;
		}
	}

	/**
	 * проверяем поле
	 *
	 * @param array $field
	 * @param array $expected_table
	 *
	 * @throws parseException
	 */
	protected static function _assertField(array $field, array $expected_table):void {

		$expected_field = self::_getNeededField($field["Field"], $expected_table["fieldList"]);

		if ($field["Null"] != "NO") {
			throw new parseException("field null");
		}

		array_walk($expected_field, function(array $expected_field) use ($field, $expected_table) {

			if (isset($expected_field["type"])) {

				$expected_field_type = $expected_field["type"];
				if (!self::_isStringEquals($field["Type"], $expected_field_type)) {
					throw new parseException("type not equels " . $field["Type"] . " != " . $expected_field_type . " in " . $expected_table["name"]);
				}
			}

			if (isset($expected_field["default"]) && !self::_isStringEquals($field["Default"], $expected_field["default"])) {
				throw new parseException("default not equals");
			}
			if (isset($expected_field["extra"]) && !self::_isStringEquals($field["Extra"], $expected_field["extra"])) {
				throw new parseException("extra not equals");
			}
		});
	}

	/**
	 * получаем нужное поле
	 *
	 * @param string $field_name
	 * @param array  $expected_field_list
	 *
	 * @return array
	 * @throws parseException
	 */
	protected static function _getNeededField(string $field_name, array $expected_field_list):array {

		if (!isset($expected_field_list[$field_name])) {
			throw new parseException("field not found");
		}
		return $expected_field_list[$field_name];
	}

	/**
	 * Получааем индексы таблицы
	 *
	 * @param string $full_database_name
	 * @param string $full_table_name
	 *
	 * @return array
	 */
	protected static function _getTableIndexList(string $full_database_name, string $full_table_name):array {

		$index_list = customSharding::pdo($full_database_name)->query("SHOW INDEX FROM `$full_table_name`;")->fetchAll();

		$grouped_index_list = [];
		foreach ($index_list as $index) {

			$index_name                                                        = mb_strtolower(trim($index["Key_name"]));
			$grouped_index_list[$index_name]["fields"][$index["Seq_in_index"]] = mb_strtolower(trim($index["Column_name"]));
			$grouped_index_list[$index_name]["uniq"]                           = $index["Non_unique"] == 0 ? 1 : 0;
		}
		return $grouped_index_list;
	}

	/**
	 * сравниваем 2 строки
	 *
	 * @param string $string_1
	 * @param string $string_2
	 *
	 * @return string
	 */
	protected static function _isStringEquals(string $string_1, string $string_2):string {

		return mb_strtolower(trim($string_1)) == mb_strtolower(trim($string_2));
	}
}