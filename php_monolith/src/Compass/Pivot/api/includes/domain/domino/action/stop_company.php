<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Выполняет остановку компании на домино.
 */
class Domain_Domino_Action_StopCompany
{
	/**
	 * Выполняет остановку активной компании на домино.
	 * Останавливает компанию только на обычных портах.
	 *
	 * <h2>Это действие не пересоздает конфиг компании,
	 * поскольку компания к моменту остановки уже должна иметь актуальный конфиг</h2>
	 *
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyNotBound
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompany_Company $company, string $unbind_reason = ""): void
	{

		try {

			// пытаемся получить активный порт, если такого нет, то останавливать нечего
			$active_port = Gateway_Db_PivotCompanyService_PortRegistry::getActiveByCompanyId($domino->domino_id, $company->company_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_Domino_Exception_CompanyNotBound("company has no active port");
		}

		// проверяем, не на сервисном ли порту развернута компания
		// сервисные гасить нужно там, где они поднимались
		if (Domain_Domino_Entity_Port_Registry::isService($active_port)) {
			throw new Domain_Domino_Exception_CompanyInOnMaintenance("company bound to service port, maintenance probably");
		}

		// запускаем отвязываем компании от порта
		if (ServerProvider::isReserveServer()) {
			Domain_Domino_Action_Port_ReserveUnbind::run($domino, $active_port, $unbind_reason);
		} else {
			Domain_Domino_Action_Port_Unbind::run($domino, $active_port, $unbind_reason);
		}

		// меняем флаг в реестре компаний
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company->domino_id, $company->company_id, [
			"is_mysql_alive" => 0,
		]);
	}
}
