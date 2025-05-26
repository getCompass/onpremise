<?php

namespace Compass\Migration;

/**
 * класс для работы миграций вверх
 */
class Type_Migration_Up {

	/**
	 * выполняем действия для миграция
	 * @throws parseException|queryException
	 * @throws paramException
	 * @long
	 */
	public static function do(int|false $version = false, string|false $database_name = false):void {

		$database_list_yaml = file_get_contents(Type_Migration_Main::DATABASE_SCHEMA_PATH);
		$database_list      = yaml_parse($database_list_yaml)["databaseList"];

		foreach ($database_list as $database) {

			if ($database_name && $database_name != $database["name"]) {
				continue;
			}

			$full_database_name_list = Type_Migration_Main::getFullDatabaseNameListFromConfig($database);

			foreach ($full_database_name_list as $full_database_name) {

				try {

					$row = Gateway_Db_CompanySystem_MigrationReleaseDatabaseList::get($full_database_name);
				} catch (cs_RowIsEmpty) {

					// если записи нет то вставляем
					$row = Gateway_Db_CompanySystem_MigrationReleaseDatabaseList::insert(
						$full_database_name,
						$database["name"],
						1,
						0,
						0,
						0,
						0,
						0,
						0,
						""
					);

					// создает базу данных
					customSharding::pdoWithoutDb()->query("CREATE SCHEMA IF NOT EXISTS `$full_database_name` DEFAULT CHARACTER SET utf8;");
					Type_System_Log::doInfoLog("Создали новую базу данных: `$full_database_name`");
				}
				if ($row->is_completed != 1) {
					throw new parseException("database is not complete !!! " . $full_database_name);
				}

				$migration_path = Type_Migration_Main::getMigrationPath($database["name"]);
				self::_doDatabase($row, $migration_path, $version);
			}
		}
	}

	/**
	 * выполняем действия над базой данных
	 * @long
	 */
	protected static function _doDatabase(Struct_Db_CompanySystem_MigrationDatabase $row, string $migration_path, int|false $version):void {

		$migration_sql_up_path = "start";

		// дополнительная проверка
		while (mb_strlen($migration_sql_up_path) > 0) {

			$expected_version = $row->current_version + 1;
			if ($version && $expected_version > $version) {

				Type_Migration_Check::do($row->current_version, $row->database_name, $migration_path, $row->full_database_name);
				return;
			}

			$migration_sql_up_path = self::_getMigrationFilePath($expected_version, $migration_path);

			// если миграции не нашли то и накатывать нечего :)
			if (mb_strlen($migration_sql_up_path) < 1) {

				Type_Migration_Check::do($row->current_version, $row->database_name, $migration_path, $row->full_database_name);
				return;
			}

			// когда дошли сюда значит все готово к миграции и нам остатеся только накатить. Блокируем бд
			$set = [
				"is_completed"       => 0,
				"expected_version"   => $expected_version,
				"last_migrated_type" => Type_Migration_Main::MIGRATION_TYPE_UP,
				"last_migrated_at"   => time(),
				"last_migrated_file" => $migration_sql_up_path,
			];
			Gateway_Db_CompanySystem_MigrationReleaseDatabaseList::set($row->full_database_name, $set);

			$sql = file_get_contents($migration_sql_up_path);

			// проверяем валидность sql
			Type_Migration_Validator::assertSql($sql);
			customSharding::pdo($row->full_database_name)->query($sql);

			// проверяем что версия ок
			Type_Migration_Check::do($expected_version, $row->database_name, $migration_path, $row->full_database_name);

			// миграция завершена успешно
			$set = [
				"is_completed"     => 1,
				"previous_version" => $row->current_version,
				"current_version"  => $expected_version,
			];

			$row->current_version = $expected_version;
			if ($expected_version > $row->highest_version) {
				$set["highest_version"] = $expected_version;
			}
			Gateway_Db_CompanySystem_MigrationReleaseDatabaseList::set($row->full_database_name, $set);
			Type_System_Log::doInfoLog("Для базы данных накатили версию: $expected_version");
		}
	}

	/**
	 * Получаем пути для миграций
	 *
	 * @param int    $expected_version
	 * @param string $migration_path
	 *
	 * @return string|false
	 * @throws parseException
	 */
	protected static function _getMigrationFilePath(int $expected_version, string $migration_path):string|false {

		if (!is_dir($migration_path)) {
			throw new parseException("migrate file not found for database");
		}

		$migration_file_scan = scandir($migration_path);

		$migration_file_list = array_diff($migration_file_scan, ["..", "."]);

		$migration_sql_up_path = "";

		foreach ($migration_file_list as $migration_file) {

			$migration_explode_list = explode("_", $migration_file);
			if (!isset($migration_explode_list[0]) || (int) $migration_explode_list[0] != $expected_version) {
				continue;
			}

			if (str_ends_with($migration_file, ".up.sql")) {
				$migration_sql_up_path = $migration_path . "/" . $migration_file;
			}
		}

		if (mb_strlen($migration_sql_up_path) < 1) {
			return "";
		}
		return $migration_sql_up_path;
	}
}