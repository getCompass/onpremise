<?php

namespace Compass\Pivot;

/**
 * Action для создания компании резервного сервера
 */
class Domain_System_Action_CreateReserveServerCompany
{
	/**
	 * Выполняем
	 */
	public static function do(int $company_id, string $domino_id, int $port, string $host): array | null
	{

		// проверяем наличие конфига компании
		try {

			Domain_Domino_Entity_Config::checkExistConfig($company_id, $domino_id);
			return null;
		} catch (Domain_Company_Exception_ConfigNotExist) {
			// если конфиг для компании отсутствует, то продолжаем
		}

		$domino_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);

		$port_row = Gateway_Db_PivotCompanyService_PortRegistry::getOne($domino_row->domino_id, $port, $host);

		// занимаем порт на доминошке
		Domain_Domino_Action_Port_ReserveBind::run($domino_row, $port_row, $company_id, Domain_Domino_Action_Port_Bind::POLICY_RESERVE_CREATING);

		// создаём/обновляем конфиг mysql для компании
		$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);

		$company->status   = Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE;
		$port_registry_row = Gateway_Db_PivotCompanyService_PortRegistry::getOne($domino_row->domino_id, $port_row->port, $port_row->host);
		Domain_Domino_Action_Config_UpdateMysql::do($company, $domino_row, $port_registry_row, true);

		$mysql_host = $port_row->host !== "" ? $port_row->host : $domino_id . "-" . $port_row->port;

		return [$mysql_host, $port_row->port];
	}
}
