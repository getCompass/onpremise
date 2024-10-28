<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Server\ServerProvider;
use PHPMailer\PHPMailer\Exception;
use PhpParser\Error;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Чистим dirty миграцию для pivot_company_service
 *
 * скрипт без dry-run, запускается сразу, безопасен для повторного выполнения
 */
class Update_Fix_Migration {

	protected const _DB_KEY            = "pivot_company_service";
	protected const _TABLE_KEY         = "schema_migrations";
	protected const _MIGRATION_VERSION = 6;

	/**
	 * работаем
	 */
	public static function doWork():void {

		if (!ServerProvider::isOnPremise()) {

			console("Для запуска только на on-premise окружении");
			return;
		}

		self::_doWork();
	}

	/**
	 * выполняем основную часть скрипта
	 * @long
	 */
	protected static function _doWork():void {

		// получаем запись в версией миграции
		// EXPLAIN: INDEX PRIMARY
		$query = "SELECT * FROM `?p` WHERE `version` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, self::_MIGRATION_VERSION, 1);

		if (!isset($row["version"])) {

			// значит миграции нет такой еще не было, выходим
			console("No migration found to fix");
			console("Exit");
			return;
		}

		// если миграция есть и она поломана
		if ($row["version"] === 6 && $row["dirty"] === 1) {

			// правим миграцию
			$set["dirty"] = 0;
			$query        = "UPDATE `?p` SET ?u WHERE `version` = ?i LIMIT ?i";
			ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, self::_MIGRATION_VERSION, 1);

			console("Migration found and fixed");
			console("Done");
			return;
		}

		console("Migration already ok");
		console("Done");
	}
}

// начинаем выполнение
Update_Fix_Migration::doWork();