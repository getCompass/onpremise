<?php

namespace Compass\Migration;

/**
 * класс управления базами данными и подключениями
 * это отдельная розетка специально создана так как как обычный ашрдинг сильно отличается
 */
class customSharding {

	// адаптер mysql для работы с уонкретной базой
	// список баз задается в конфиге sharding.php
	public static function pdo(string $db):myPDObasic {

		$db_key = $GLOBALS["MYSQL_HOST"] . "_" . $db;
		if (!isset($GLOBALS["pdo_driver"][$db_key])) {

			// если нет вообще массива
			if (!isset($GLOBALS["pdo_driver"])) {
				$GLOBALS["pdo_driver"] = [];
			}

			// этот модуль умеет общаться с любой базой данных не зависимо от того есть она в конфиге шардинг или нет
			$mysql_host = $GLOBALS["MYSQL_HOST"] . ":" . $GLOBALS["MYSQL_PORT"];
			$mysql_user = $GLOBALS["MYSQL_USER"];
			$mysql_pass = $GLOBALS["MYSQL_PASS"];

			// создаем соединение
			$GLOBALS["pdo_driver"][$db_key] = self::pdoConnect(
				$mysql_host,
				$mysql_user,
				$mysql_pass,
				false,
				$db
			);
		}

		return $GLOBALS["pdo_driver"][$db_key];
	}

	// адаптер mysql для работы когда базы нет
	public static function pdoWithoutDb():myPDObasic {

		$mysql_host = $GLOBALS["MYSQL_HOST"] . ":" . $GLOBALS["MYSQL_PORT"];
		$mysql_user = $GLOBALS["MYSQL_USER"];
		$mysql_pass = $GLOBALS["MYSQL_PASS"];

		// создаем соединение
		return self::pdoConnect(
			$mysql_host,
			$mysql_user,
			$mysql_pass,
			false
		);
	}

	// функция для создания соединения с MySQL сервером
	public static function pdoConnect(string $host, string $user, string $password, bool $ssl, string $db = null):myPDObasic {

		// опции подключения
		$opt = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => true,  // ! Важно чтобы было TRUE
			PDO::ATTR_STATEMENT_CLASS    => ["myPDOStatement"],
		];

		// если подключение зашифровано
		if ($ssl == true) {

			$opt[PDO::MYSQL_ATTR_SSL_CIPHER]             = "DHE-RSA-AES256-SHA:AES128-SHA";
			$opt[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
		}

		// собираем DSN строку подключения
		$dsn = "mysql:host=$host;";
		if (!is_null($db)) {
			$dsn .= "dbname=$db;";
		}
		$dsn .= "charset=utf8mb4;";

		return new myPDObasic($dsn, $user, $password, $opt);
	}
}