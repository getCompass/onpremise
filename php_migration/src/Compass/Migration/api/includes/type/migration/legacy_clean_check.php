<?php

namespace Compass\Migration;

/**
 * класс для проверки миграций по удалению легаси
 */
class Type_Migration_LegacyCleanCheck {

	/**
	 * метод для старта проверки миграций
	 *
	 * @long
	 */
	public static function do(int $version, string $migration_path, int $release_version):void {

		$migration_yaml_path = self::_getMigrationYamlFilePath($version, $migration_path);

		// проверяем что версию можно накатить
		$expected_db_struct = yaml_parse(file_get_contents($migration_yaml_path));

		self::_assertDatabase($expected_db_struct, $release_version);
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
	protected static function _getMigrationYamlFilePath(int $expected_version, string $migration_path):string {

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
	 * @param array $yaml_config
	 * @param int   $release_version
	 *
	 * @throws parseException
	 */
	protected static function _assertDatabase(array $yaml_config, int $release_version):void {

		$needed_release_version = $yaml_config["needDatabaseVersion"];

		if ($needed_release_version != $release_version) {
			throw new parseException("invalid release database version, cant delete legacy");
		}
	}
}