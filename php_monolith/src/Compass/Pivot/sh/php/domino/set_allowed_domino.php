<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

	console("Делает доминошку доступной для создания компаний");
	console("Параметры:");
	console("--domino-id - id доминошки");
	exit;
}

$domino_id = Type_Script_InputParser::getArgumentValue("--domino-id", Type_Script_InputParser::TYPE_STRING, "", false);
if (mb_strlen($domino_id) < 1) {

	console("Введите id domino");
	$domino_id = trim(readline());
	if (mb_strlen($domino_id) < 1) {

		console(redText("Не доступен пустой идентификатор"));
		exit(1);
	}
}

try {

	$domino_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);
} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

	console(redText("Такая доминошка не существует"));
	exit(1);
}

Gateway_Db_PivotCompanyService_DominoRegistry::set($domino_id, ["is_company_creating_allowed" => 1]);