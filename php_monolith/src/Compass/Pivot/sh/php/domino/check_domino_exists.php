<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\RowNotFoundException;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Класс проверки, что домино существует
 */
class Domino_CheckDominoExists {

	public static function run(string $domino_id):void {

		try {
			Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);
		} catch (RowNotFoundException) {

			console(redText("Не удалось найти доминошку " . $domino_id));
			exit(1);
		}
	}

}

// если прислали аргумент --help
if (Type_Script_InputHelper::needShowUsage()) {

	console("Данный скрипт проверяет наличие доминошки ");
	console("--domino-id - проверяемое домино");
	console("Запустите скрипт без флага --help, чтобы начать");

	exit(0);
}

$domino_id = Type_Script_InputParser::getArgumentValue("--domino-id", Type_Script_InputParser::TYPE_STRING, "", true);

// запускаем скрипт
Domino_CheckDominoExists::run($domino_id);