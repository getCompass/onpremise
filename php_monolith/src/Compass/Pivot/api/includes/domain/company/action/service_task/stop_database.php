<?php

namespace Compass\Pivot;

/**
 * Гасим mysql компанию в гибернацию.
 */
class Domain_Company_Action_ServiceTask_StopDatabase implements Domain_Company_Action_ServiceTask_Main {

	/**
	 * Ввести компанию в гибернацию.
	 *
	 * @param Struct_Db_PivotCompany_Company                $company_row
	 * @param Struct_Db_PivotCompanyService_CompanyRegistry $company_registry
	 * @param \BaseFrame\System\Log                         $log
	 * @param array                                         $data
	 *
	 * @return \BaseFrame\System\Log
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyNotBound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \returnException
	 * @long
	 * @throws \returnException
	 */
	public static function do(Struct_Db_PivotCompany_Company $company_row, Struct_Db_PivotCompanyService_CompanyRegistry $company_registry, \BaseFrame\System\Log $log, array $data = []):\BaseFrame\System\Log {

		if ($company_row->status != Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED || !$company_registry->is_hibernated) {

			$log_text = "Компания {$company_row->company_id} не находится в гибернации, завершаю таск...";

			if (!isTestServer()) {
				Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_SERVICE, $log_text);
			}

			return $log->addText($log_text);
		}

		if (!$company_registry->is_mysql_alive) {
			return $log->addText("Компания уже спит");
		}

		if ($company_row->status == Domain_Company_Entity_Company::COMPANY_STATUS_DELETED) {

			$log->addText("company deleted");
			return $log;
		}

		try {

			$domino = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company_row->domino_id);
			$port   = Gateway_Db_PivotCompanyService_PortRegistry::getActiveByCompanyId($company_row->domino_id, $company_row->company_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_Company_Exception_ConfigNotExist("no domino port assigned to company");
		}

		// генерим обновленный конфиг для компании
		Domain_Domino_Action_Config_UpdateMysql::do($company_row, $domino, need_force_update: true);
		Domain_Domino_Action_WaitConfigSync::do($company_row, $domino);
		Domain_Domino_Action_StopCompany::run($domino, $company_row, "stop_database");

		// сохраним аналитику по гибернации
		Type_System_Analytic::save($company_row->company_id, $company_row->domino_id, Type_System_Analytic::TYPE_POST_HIBERNATE);

		Type_Phphooker_Main::onCountCompany(time());

		return $log->addText("Остановлена база данных в мире {$company_row->domino_id} под портом {$port->port} для компании {$port->company_id}");
	}
}