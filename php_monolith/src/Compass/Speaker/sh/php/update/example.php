<?php

namespace Compass\Speaker;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт-пример - как обновлять что-либо
 * В примере скрипт просто пробегается по таблицам thread meta
 * И ничего не делает
 */
class Script_Update_Release45_Example {

	// лимит для основного sql запроса
	protected const _QUERY_LIMIT = 5000;

	// работаем с одной таблицей
	public static function doWork(string $sharding_key, string $table_key):void {

		$offset = 0;
		do {

			$query = "SELECT * FROM `?p` WHERE ?i LIMIT ?i OFFSET ?i";
			$list  = ShardingGateway::database($sharding_key)->getAll($query, $table_key, 1, self::_QUERY_LIMIT, $offset);

			// работаем с каждой записью
			foreach ($list as $row) {
				self::_doWorkOneRow($row);
			}

			$offset += self::_QUERY_LIMIT;
		} while (count($list) == self::_QUERY_LIMIT);
	}

	// работаем с одной записью
	protected static function _doWorkOneRow(array $row):void {

		if (isDryRun()) {

			console($row);
			console("ROW — DRY RUN");
			return;
		}
		console("ROW — OK");
	}
}

// получаем список баз
$database_list = Type_Script_Helper::getDatabaseList("#^thread_\d{4}_\d{1,2}$#si");

// проходимся по всем таблицам
foreach ($database_list as $v1) {

	// получаем список таблиц из базы
	$table_list = Type_Script_Helper::getOneDbTableList($v1, "#^meta_\d{1,2}$#si");

	// проходимся по каждой таблице
	foreach ($table_list as $v2) {

		// работаем с одной таблицей
		Script_Update_Release45_Example::doWork($v1, $v2);
	}
}

// успешное выполнение скрипта
console("DONE");