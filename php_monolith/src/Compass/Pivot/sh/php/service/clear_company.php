<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

console("Введите company_id которую надо почистить");
$company_id = intval(trim(readline()));
if ($company_id < 1) {

	console("Передан неверный company_id");
	exit(1);
}

try {

	$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
} catch (\cs_RowIsEmpty) {

	console("c" . $company_id . ": Компания не найдена");
	exit(1);
}

console("c" . $company->company_id . ": Вы действительно хотите почистить компанию " . $company->name . " ? y/n");
$result = readline();

if ($result != "y") {

	console("Действие отменено");
	exit(1);
}

Domain_System_Action_PurgeCompany::do($company);
Domain_System_Action_CheckReadyCompany::do($company);

console(greenText("c" . $company->company_id . ": Компания успешно почищена и готова"));