<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$target_domino_id = Type_Script_InputParser::getArgumentValue("target-domino-id", Type_Script_InputParser::TYPE_STRING);
$company_id       = Type_Script_InputParser::getArgumentValue("company-id", Type_Script_InputParser::TYPE_INT);

$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);

$data = [
	"target_domino_id"              => $target_domino_id,
	"source_domino_id"              => $company->domino_id,
	"delay_before_block_company"    => Type_Script_InputParser::getArgumentValue("delay-block", Type_Script_InputParser::TYPE_INT),
	"delay_before_relocate_company" => Type_Script_InputParser::getArgumentValue("delay-relocate", Type_Script_InputParser::TYPE_INT),
	"need_to_be_active_after"       => $company->status === Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE || $company->status === Domain_Company_Entity_Company::COMPANY_STATUS_VACANT,
];

if (Type_Script_InputParser::getArgumentValue("--no-task", Type_Script_InputParser::TYPE_NONE, false, false)) {

	$data["need_skip_schedule"] = true;

	$log              = new \BaseFrame\System\Log();
	$company_registry = Gateway_Db_PivotCompanyService_CompanyRegistry::getOne($company->domino_id, $company_id);

	try {

		Domain_Company_Action_ServiceTask_RelocateCompanyData::do($company, $company_registry, $log, $data);
	} finally {

		$log->close();
		console($log->text);
	}
} else {

	// добавляем задачу для остановки базы данных компании
	$task_id = Domain_Company_Entity_ServiceTask::schedule(
		Domain_Company_Entity_ServiceTask::TASK_TYPE_RELOCATION_STEP_ONE,
		time(),
		$company_id,
		$data
	);

	// помечаем, что начался процесс переезда
	$company_tier = Gateway_Db_PivotCompany_CompanyTierObserve::get($company_id);
	Domain_Company_Entity_Tier::markRelocatingStarted($company_tier, $task_id);
}
