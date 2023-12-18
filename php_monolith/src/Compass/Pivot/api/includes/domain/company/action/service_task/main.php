<?php

namespace Compass\Pivot;

/**
 * Интерфейс для сервисных тасков
 */
interface Domain_Company_Action_ServiceTask_Main {

	/**
	 * Выполнить таск
	 *
	 * @param Struct_Db_PivotCompany_Company                $company_row
	 * @param Struct_Db_PivotCompanyService_CompanyRegistry $company_registry
	 * @param \BaseFrame\System\Log                         $log
	 *
	 * @return \BaseFrame\System\Log
	 */
	public static function do(Struct_Db_PivotCompany_Company $company_row, Struct_Db_PivotCompanyService_CompanyRegistry $company_registry, \BaseFrame\System\Log $log, array $data = []):\BaseFrame\System\Log;

}