<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

	console("Скрипт создает новую компанию и закрепляет ее за пользователем");
	console("Параметры:");
	console("  --creator — кто создал компанию");
	console("  --name    — название компании");
	exit;
}

$created_by_user_id = Type_Script_InputParser::getArgumentValue("--creator");
$company_name       = Type_Script_InputParser::getArgumentValue("--name");

[$company] = Domain_Company_Action_Take::do($created_by_user_id, 1, $company_name, generateUUID(), "", false);
console("Компания создана с идентификатором {$company->company_id}, url: {$company->url}");