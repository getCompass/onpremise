<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Класс проверки, что команда существует
 */
class Domino_CheckTeamExists {

	public static function run():void {

		$company_list = Gateway_Db_PivotCompany_CompanyList::getByStatusList([Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE], 1);

		// если команда есть, возвращаем 0
		if ($company_list !== []) {
			exit(0);
		}

		exit(1);
	}

}

// если прислали аргумент --help
if (Type_Script_InputHelper::needShowUsage()) {

	console("Данный скрипт проверяет наличие команд ");
	console("Запустите скрипт без флага --help, чтобы начать");

	exit(0);
}

// запускаем скрипт
Domino_CheckTeamExists::run();