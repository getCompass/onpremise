<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для работы с задачей релокации.
 */
class Domain_Company_Entity_ServiceTask_Relocation {

	/**
	 * Проверяет компанию на пригодность для релокации.
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public static function assertCompany(Struct_Db_PivotCompany_Company $company):void {

		// проверяем, что компанию можно запушить на релокацию
		if ($company->status === Domain_Company_Entity_Company::COMPANY_STATUS_VACANT) {
			throw new \BaseFrame\Exception\Request\ParamException("vacant company can not be relocated");
		}
	}

	/**
	 * Проверяет, что на указанное домино возможна релокация.
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public static function assertDomino(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompanyService_DominoRegistry $target_domino):void {

		if ($company->domino_id === $target_domino->domino_id) {
			throw new \BaseFrame\Exception\Request\ParamException("current and target domino are same");
		}

		if ($target_domino->common_active_port_count >= $target_domino->common_port_count) {
			throw new \BaseFrame\Exception\Request\ParamException("domino has no void port");
		}
	}

	/**
	 * Получаем домино, пригодное для релокации компании
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function resolveDomino(Struct_Db_PivotCompany_Company $company):Struct_Db_PivotCompanyService_DominoRegistry {

		try {

			// пытаемся сами получить домино для релокации
			return Gateway_Db_PivotCompanyService_DominoRegistry::getOneForRelocate($company->domino_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// нет свободных домино для релокации
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("there is no void ports");
		}
	}

	/**
	 * Закидывает задачу релокации в планировщик .
	 *
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws \queryException
	 */
	public static function schedule(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompanyService_DominoRegistry $target_domino, int $task_delay, int $delay_block, int $delay_relocate):int {

		// передаем задачу в планировщик
		return Domain_Company_Entity_ServiceTask::schedule(Domain_Company_Entity_ServiceTask::TASK_TYPE_RELOCATION_STEP_ONE, time() + $task_delay, $company->company_id, [
			"target_domino_id"              => $target_domino->domino_id,
			"source_domino_id"              => $company->domino_id,
			"delay_before_block_company"    => $delay_block,
			"delay_before_relocate_company" => $delay_relocate,
			"need_to_be_active_after"       => $company->status === Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE,
		]);
	}
}