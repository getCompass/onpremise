<?php

namespace Compass\Pivot;

/**
 * Действие по применению скопированных данных при переезде компании.
 */
class Domain_Domino_Action_Relocation_ApplyCompanyData {

	/**
	 * Применяет перенесенные данные на домино.
	 * Второй шаг переезда компании.
	 *
	 * !!! Не пересоздает конфиги, только восстанавливает базу данных из дампа.
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino
	 * @param Struct_Db_PivotCompanyService_PortRegistry   $service_port
	 * @param Struct_Db_PivotCompanyService_PortRegistry   $common_port
	 * @param Struct_Db_PivotCompany_Company               $company
	 *
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $service_port, Struct_Db_PivotCompanyService_PortRegistry $common_port, Struct_Db_PivotCompany_Company $company):void {

		try {

			// пытаемся получить активный порт, если такого нет, то останавливать нечего
			// если вдруг получили, то что-то явно идет не так, и делать ничего нельзя
			$active_port = Gateway_Db_PivotCompanyService_PortRegistry::getActiveByCompanyId($domino->domino_id, $company->company_id);
			throw new Domain_Domino_Exception_CompanyIsBound("company is already bound to port {$active_port->port}");
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// это нормально, просто проверили, что компания не привязана к порту
		}

		try {

			// первым делом привязываем сервисный порт для накатки данных
			Domain_Domino_Action_Port_Bind::run($domino, $service_port, $company->company_id, Domain_Domino_Action_Port_Bind::POLICY_RELOCATE_APPLYING);

			// переносим данные компании
			Gateway_Bus_DatabaseController::beginDataApplying($domino, $company->company_id);
			Domain_Domino_Action_Port_Unbind::run($domino, $service_port, "applyCompanyData");
		} catch (\Exception $e) {

			Domain_Domino_Action_Port_Invalidate::run($domino, $service_port, "error on begin data applying");
			Domain_Domino_Action_Port_Unlock::run($domino, $common_port);

			throw $e;
		}
	}
}