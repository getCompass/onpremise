<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$company_id         = Type_Script_InputParser::getArgumentValue("company-id", Type_Script_InputParser::TYPE_INT);
$is_force_hibernate = Type_Script_InputParser::getArgumentValue("is-force", Type_Script_InputParser::TYPE_INT, 0, false);
$is_force_hibernate = $is_force_hibernate === 1;
$company            = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);

if (Type_Script_InputParser::getArgumentValue("--no-task", Type_Script_InputParser::TYPE_NONE, false, false)) {

	$log              = new \BaseFrame\System\Log();
	$company_registry = Gateway_Db_PivotCompanyService_CompanyRegistry::getOne($company->domino_id, $company_id);

	Domain_Company_Action_ServiceTask_Hibernate::do($company, $company_registry, $log, is_force_hibernate: $is_force_hibernate);

	$company_registry = Gateway_Db_PivotCompanyService_CompanyRegistry::getOne($company->domino_id, $company_id);
	$company          = Gateway_Db_PivotCompany_CompanyList::getOne($company->company_id);
	Domain_Company_Action_ServiceTask_StopDatabase::do($company, $company_registry, $log);

	$log->close();
	console($log->text);

	$domino  = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company->domino_id);
	$company = Gateway_Db_PivotCompany_CompanyList::getOne($company->company_id);

	Domain_Domino_Action_Config_UpdateMysql::do($company, $domino);
} else {

	// добавляем задачу для остановки базы данных компании
	Domain_Company_Entity_ServiceTask::schedule(
		Domain_Company_Entity_ServiceTask::TASK_TYPE_HIBERNATION_STEP_ONE,
		time(),
		$company_id,
	);
}