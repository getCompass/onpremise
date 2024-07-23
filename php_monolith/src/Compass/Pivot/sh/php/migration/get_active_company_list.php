<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use PhpParser\Error;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для получения списка активных компаний
 */
class Migration_Get_Active_Company_List {

	/**
	 * стартовая функция скрипта
	 */
	public function run():void {

		$company_list = Gateway_Db_PivotCompany_CompanyList::getActiveList();

		foreach ($company_list as $company) {
			console("name={$company->name} company_id={$company->company_id} created_by_user_id={$company->created_by_user_id} url={$company->url}");
		}
	}
}

// запускаем
(new Migration_Get_Active_Company_List())->run();