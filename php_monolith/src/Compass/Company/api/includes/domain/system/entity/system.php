<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с системой
 */
class Domain_System_Entity_System {

	// список защищенных баз
	const _PROTECTED_DATABASE_LIST = [
		"information_schema",
		"mysql",
		"performance_schema",
		"sys",
	];

	// список защищенных таблиц
	const _PROTECTED_TABLES_LIST = [
		"go_event_subscriber_list", // таблица с подписками, пользовательской инфы там нет, чистить не нужно
		"migration_release_database_list", // база данных с версиями мигарций таблиц, ее нельзя чистить
		"migration_cleaning_database_list", // база данных с версиями мигарций таблиц, ее нельзя чистить
	];

	/**
	 * Очищает таблицы базы данных.
	 * @long
	 */
	public static function purgeDatabase():void {

		// выбрасываем исключение, если это не тестовый сервер
		if (!isTestServer()) {
			throw new \parseException("unexpected behaviour, cant purge database on this environment");
		}

		try {

			$company_mysql      = getCompanyConfig("COMPANY_MYSQL");
			$company_mysql_host = $company_mysql["host"] ?? "";
			$company_mysql_port = $company_mysql["port"] ?? "";
			$company_mysql_user = $company_mysql["user"] ?? "";
			$company_mysql_pass = $company_mysql["pass"] ?? "";

			$connect   = \sharding::pdoConnect(
				$company_mysql_host . ":" . $company_mysql_port,
				$company_mysql_user,
				$company_mysql_pass,
				false);
			$databases = $connect->query("SHOW DATABASES;")->fetchAll();

			foreach ($databases as $database) {

				if (in_array($database["Database"], self::_PROTECTED_DATABASE_LIST)) {
					continue;
				}
				$query_list = [];
				$connect->query("use " . $database["Database"] . ";");

				$tables = $connect->query("SHOW TABLES;")->fetchAll(\PDO::FETCH_COLUMN);
				foreach ($tables as $table) {

					//
					if (in_array($table, self::_PROTECTED_TABLES_LIST)) {
						continue;
					}

					$query_list[] = "DELETE FROM `" . $database["Database"] . "`.`" . $table . "`;";
				}

				// склеиваем все отдельные запросы в один
				$query = implode("\n", $query_list);

				// делаем запрос
				$connect->query($query);

				// дебажим запросы
				Type_System_Admin::log("purge_database_queries", $query_list);
			}
		} catch (\Exception $e) {

			// логируем ошибку
			Type_System_Admin::log("purge_database", ["title" => "is_failed", "message" => $e->getMessage()]);
		}
	}

	/**
	 * Проверяем что все таблицы чисты
	 * @long
	 */
	public static function checkCleared():void {

		$company_mysql      = getCompanyConfig("COMPANY_MYSQL");
		$company_mysql_host = $company_mysql["host"] ?? "";
		$company_mysql_port = $company_mysql["port"] ?? "";
		$company_mysql_user = $company_mysql["user"] ?? "";
		$company_mysql_pass = $company_mysql["pass"] ?? "";

		$connect   = \sharding::pdoConnect(
			$company_mysql_host . ":" . $company_mysql_port,
			$company_mysql_user,
			$company_mysql_pass,
			false);
		$databases = $connect->query("SHOW DATABASES;")->fetchAll();

		foreach ($databases as $database) {

			if (in_array($database["Database"], self::_PROTECTED_DATABASE_LIST)) {
				continue;
			}

			$connect->query("use " . $database["Database"] . ";");
			$tables = $connect->query("SHOW TABLES;")->fetchAll(\PDO::FETCH_COLUMN);
			foreach ($tables as $table) {

				$query    = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i;";
				$response = $connect->getOne($query, $table, 1);
				if ($response["count"] != 0) {
					throw new cs_TableIsNotEmpty($table);
				}
			}
		}
	}
}
