<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для обновления данных компании.
 */
class Script_Exec_Company_Update_Script extends Type_Script_CompanyUpdateTemplate {

	/**
	 * Функция прехука, вызывается до исполнения логики.
	 * Тут можно запросить дополнительные данные из консоли.
	 */
	protected function _preExec():void {

		console(purpleText("executing script " . static::class));
	}

	/**
	 * Исполняющая функция.
	 * Если нужен особенный скрипт, то нужно в дочернем классе переопределить эту функцию.
	 *
	 * В эту функцию по очереди будут поступать все компании, которые удалось получить по идентификаторам.
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _exec(Struct_Db_PivotCompany_Company $company):void {

		$script_name = Type_Script_CompanyUpdateInputHelper::getScriptName();

		if ($this->_is_dry_run) {

			$this->_log(purpleText("{$script_name} dry-run call for company {$company->company_id}... "));
		}

		[$script_log, $error_log] = Gateway_Socket_Company::execCompanyUpdateScript(
			Type_Script_CompanyUpdateInputHelper::getScriptName(),
			Type_Script_CompanyUpdateInputHelper::getScriptData(),
			$this->_makeMask(),
			$company->company_id,
			$company->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($company->extra),
			Type_Script_CompanyUpdateInputHelper::getModuleProxy(),
		);

		$this->_writeErrorLog($company, $error_log);
		$this->_writeLog($company, $script_log);
	}
}

(new Script_Exec_Company_Update_Script())->run();