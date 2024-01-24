<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

// какие компании биндим
$company_id_list = Type_Script_InputParser::getArgumentValue("company-id-list", Type_Script_InputParser::TYPE_ARRAY, false, false);

if (!$company_id_list) {
	$company_list = Gateway_Db_PivotCompany_CompanyList::getFullList();
} else {
	$company_list = Gateway_Db_PivotCompany_CompanyList::getList($company_id_list);
}

$company_json_list = [];

foreach ($company_list as $company) {

	if ($company->status != Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
		continue;
	}

	// создаем запись в company_init_registry
	createCompanyInitRegistry($company->company_id);

	// получаем домино
	$domino_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company->domino_id);

	// генерим новый урл для компании
	$company->url = makeCompanyUrl($company->company_id, $domino_row);

	// устанавливаем новый урл для компании
	Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
		"url" => $company->url,
	]);

	// добавляем в company_registry
	$company_registry = new Struct_Db_PivotCompanyService_CompanyRegistry($company->company_id, 0, 0, 0, time(), 0);
	Gateway_Db_PivotCompanyService_CompanyRegistry::insert($company->domino_id, $company_registry);

	// лочим порт на час
	$port_row = Domain_Domino_Action_Port_LockForCompany::run(
		$domino_row, $company->company_id, Domain_Domino_Entity_Port_Registry::TYPE_COMMON, HOUR1);

	// биндим порт
	Domain_Domino_Action_Port_Bind::run($domino_row, $port_row, $company->company_id, Domain_Domino_Action_Port_Bind::POLICY_CREATING);
	Gateway_Db_PivotCompanyService_CompanyRegistry::set($company->domino_id, $company->company_id, ["is_mysql_alive" => 1]);

	// поднимаем актуальную миграцию
	Gateway_Bus_DatabaseController::migrateUp($domino_row, $company->company_id);

	// пишем логи, что создали компанию
	/** начало транзакции */
	Gateway_Db_PivotCompanyService_Main::beginTransaction();

	$company_registry       = Gateway_Db_PivotCompanyService_CompanyInitRegistry::getForUpdate($company->company_id);
	$company_registry->logs = Domain_Company_Entity_InitRegistry_Logs::addCompanyCreateSuccessLog($company_registry->logs);

	Gateway_Db_PivotCompanyService_CompanyInitRegistry::set($company->company_id, [
		"logs" => $company_registry->logs,
	]);
	Gateway_Db_PivotCompanyService_Main::commitTransaction();

	// биндим порт
	$port_row = Gateway_Db_PivotCompanyService_PortRegistry::getOne($domino_row->domino_id, $port_row->port);

	// обновляем конфиг
	Domain_Domino_Action_Config_UpdateMysql::do($company, $domino_row, $port_row);

	// записываем в json, который потребуется для накатки баз
	$company_json_list[$domino_row->domino_id]["c{$company->company_id}"] = $port_row->port;

	// записываем лог, что компания занята
	Gateway_Db_PivotCompanyService_Main::beginTransaction();

	// получаем горячую свободную компанию
	$company_init_registry = Gateway_Db_PivotCompanyService_CompanyInitRegistry::getForUpdate($company->company_id);

	$company_init_registry->logs = Domain_Company_Entity_InitRegistry_Logs::addCompanyFinishedOccupationLog($company_init_registry->logs);
	$set                         = [
		"occupation_finished_at" => time(),
		"occupant_user_id"       => $company->created_by_user_id,
		"logs"                   => $company_init_registry->logs,
	];
	Gateway_Db_PivotCompanyService_CompanyInitRegistry::set($company_init_registry->company_id, $set);
	Gateway_Db_PivotCompanyService_Main::commitTransaction();
}

// пишем итоговый json в файл
foreach ($company_json_list as $key => $domino_item) {
	writeToFile(PATH_TEMP . "company_list_{$key}.json", json_encode($domino_item));
}

function createCompanyInitRegistry(int $company_id):void {

	$company_registry = new Struct_Db_PivotCompanyService_CompanyInitRegistry(
		$company_id,
		0,
		0,
		0,
		time(),
		0,
		0,
		0,
		0,
		0,
		0,
		time(),
		0,
		0,
		0,
		[],
		[],
	);
	Gateway_Db_PivotCompanyService_CompanyInitRegistry::insert($company_registry);
}

/**
 * Возвращает строку с адресом,
 * по которому можно будет достучаться до компании извне.
 */
function makeCompanyUrl(int $company_id, Struct_Db_PivotCompanyService_DominoRegistry $domino_row):string {

	$url = Domain_Domino_Entity_Registry_Extra::getUrl($domino_row->extra);
	return "c$company_id-$url";
}