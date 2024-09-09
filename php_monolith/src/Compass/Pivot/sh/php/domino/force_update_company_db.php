<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$domino_list = Gateway_Db_PivotCompanyService_DominoRegistry::getAll();

foreach ($domino_list as $domino) {

	// получаем все порты с пространствами
	$port_list = Gateway_Db_PivotCompanyService_PortRegistry::getAllWithCompany($domino->domino_id);

	// получаем список компаний
	$company_id_list    = array_column($port_list, "company_id");
	$company_list_assoc = [];
	$company_list       = Gateway_Db_PivotCompany_CompanyList::getList($company_id_list);

	foreach ($company_list as $company) {
		$company_list_assoc[$company->company_id] = $company;
	}

	// обновляем деплой для домино
	Gateway_Bus_DatabaseController::updateDeployment($domino);

	// для каждого пространства, которая занимает порт обновляем конфиг
	foreach ($port_list as $port) {

		if (!isset($company_list_assoc[$port->company_id])) {
			console("Space $port->company_id from port $port->port does not exist in company_list");
			continue;
		}

		// генерим конфиг мускула для компании в активном состоянии
		Domain_Domino_Action_Config_UpdateMysql::do($company_list_assoc[$port->company_id], $domino, $port, true);
	}
}

