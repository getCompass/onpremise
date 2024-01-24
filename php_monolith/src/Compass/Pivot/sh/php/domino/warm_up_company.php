<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

// проверяем, что запустил скрипт именно www-data (uid 33)
if (posix_geteuid() !== 33 ) {

	console("Запусти меня от пользователя www-data");
	exit;
}

if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

	console("Прогревает одну компанию");
	exit;
}

try {
	$domino_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOneForCreate();
} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

	console(redText("Такая доминошка не существует"));
	exit(1);
}

try {
	$company_id = Domain_Domino_Action_CreateVacantCompany::do($domino_row);
	console("created company {$company_id}");
} catch (\Exception $e) {

	console(redText("Не смогли прогреть компанию"));
	console($e->getMessage());
	console($e->getTraceAsString());
	exit(1);
}
