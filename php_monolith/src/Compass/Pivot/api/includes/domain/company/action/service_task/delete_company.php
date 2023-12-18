<?php

namespace Compass\Pivot;

/**
 * Гасим mysql компании при удаления
 */
class Domain_Company_Action_ServiceTask_DeleteCompany implements Domain_Company_Action_ServiceTask_Main {

	/**
	 * удаляем компанию
	 */
	public static function do(Struct_Db_PivotCompany_Company $company_row, Struct_Db_PivotCompanyService_CompanyRegistry $company_registry, \BaseFrame\System\Log $log, array $data = []):\BaseFrame\System\Log {

		if ($company_row->status != Domain_Company_Entity_Company::COMPANY_STATUS_DELETED) {
			return $log->addText("Компания не в нужном статусе, завершаю таск...");
		}

		if (!$company_registry->is_mysql_alive) {
			return $log->addText("Компания уже спит");
		}

		// освобождаем порт от компании
		try {

			$domino_registry_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company_row->domino_id);
			$port_registry_row   = Gateway_Db_PivotCompanyService_PortRegistry::getActiveByCompanyId($company_row->domino_id, $company_row->company_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_Company_Exception_ConfigNotExist("no domino port assigned to company");
		}

		// генерим обновленный конфиг для компании
		Domain_Domino_Action_Config_UpdateMysql::do($company_row, $domino_registry_row, need_force_update: true);
		Domain_Domino_Action_WaitConfigSync::do($company_row, $domino_registry_row);
		Domain_Domino_Action_StopCompany::run($domino_registry_row, $company_row, "deleteCompany");

		// удаляем поисковый индекс компании
		// ВНИМАНИЕ! не работает на тестовых серверах так как ломает миграции после пересоздания компании
		if (!isTestServer()) {
			Gateway_Bus_DatabaseController::dropSearchTable($domino_registry_row, $company_row->company_id);
		}

		// сохраним аналитику по гибернации
		Type_System_Analytic::save($company_row->company_id, $company_row->domino_id, Type_System_Analytic::TYPE_UNBIND_PORT_ON_DELETE_COMPANY);
		return $log->addText("Остановлена база данных в мире $company_row->domino_id под портом $port_registry_row->port для компании $port_registry_row->company_id");
	}
}