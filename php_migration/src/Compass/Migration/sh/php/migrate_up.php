<?php

namespace Compass\Migration;

use BaseFrame\Exception\Domain\ParseFatalException;

require_once __DIR__ . "/../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$help = Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false);

if ($help === true) {

	Type_System_Log::doInfoLog("Скрипт для того чтобы накатить новые миграции
	--company-list - массив компаний для которых надо накатить миграции
	--version - до какой версии включительно накатить миграции
	--database - для какой базы данных надо накатить миграции
	--start_param - параметры для запуска скрипта (y - сразу запустить выполнение скрипта)
	Все параметры optional и их можно не передавать
	");
	exit(0);
}
$version         = Type_Script_InputParser::getArgumentValue("--version", Type_Script_InputParser::TYPE_INT, false, false);
$database        = Type_Script_InputParser::getArgumentValue("--database", Type_Script_InputParser::TYPE_STRING, false, false);
$start_param     = Type_Script_InputParser::getArgumentValue("--start_param", Type_Script_InputParser::TYPE_STRING, false, false);
$company_id_list = Type_Script_InputParser::getArgumentValue("--company-list", Type_Script_InputParser::TYPE_ARRAY, [], false);
$auto_confirm    = Type_Script_InputParser::getArgumentValue("--y", Type_Script_InputParser::TYPE_NONE, false, false);

if (!$auto_confirm) {

	if (!Type_Script_InputParser::isConfirmScript("Начинаем накатывать миграции? y/n", $start_param)) {

		console("Скрипт не был запущен!!!");
		exit(1);
	}
}

Type_System_Log::doInfoLog("Начинаем обновлять компанию");

try {
	Domain_Domino_Scenario_Cli::migrate($company_id_list, Domain_Domino_Scenario_Cli::MIGRATION_TYPE_UP);
} catch (ParseFatalException|\Error $exception) {

	Type_System_Log::doErrorLog($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
	exit(1);
}
if ($database !== false) {

	Type_System_Log::doCompleteLog("Базу данных $database обновили/проверили на актуальность");
	exit(0);
}

Type_System_Log::doCompleteLog("Все базы данных обновили/проверили на актуальность");
