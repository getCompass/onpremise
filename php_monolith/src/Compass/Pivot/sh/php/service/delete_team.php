<?php

use Compass\Pivot\Domain_Company_Action_Delete;
use Compass\Pivot\Domain_User_Entity_OnpremiseRoot;
use Compass\Pivot\Gateway_Db_PivotCompany_CompanyList;
use Compass\Pivot\Type_Script_InputParser;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

// получаем id компании
$company_id = Type_Script_InputParser::getArgumentValue("--company_id", Type_Script_InputParser::TYPE_INT, 0);
$confirm    = Type_Script_InputParser::getArgumentValue("--confirm", Type_Script_InputParser::TYPE_INT, 0);

try {

	$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
} catch (\cs_RowIsEmpty) {

	console(redText("c" . $company_id . ": Компания не найдена"));
	exit(1);
}

// если компанию уже удалили
if ($company->is_deleted && $confirm == 1) {
	console("Компания {$company->name} уже удалена.");
	exit(1);
}

$company_list = Gateway_Db_PivotCompany_CompanyList::getByStatusList([2], 10000, 0);
if (count($company_list) == 1 && $confirm != 1) {
	console(redText("Вы пытаетесь удалить единственную команду на сервере!"));
}

// выходим
if ($confirm != 1) {
	exit(1);
}

// получаем id дефолтного пользователя
$default_root_user_id = Domain_User_Entity_OnpremiseRoot::getUserId();

// удаляем компанию
try {

	Domain_Company_Action_Delete::do($default_root_user_id, $company);
} catch (cs_CompanyUserIsNotOwner) {

	console(redText("Пользователь не является администратором компании"));
	exit(1);
}

console(greenText("Успешно удалили компанию {$company->name}"));