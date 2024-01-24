<?php

namespace Compass\Pivot;

/**
 * Действие — первый этап переезда компании — перенос данных.
 */
class Domain_Domino_Action_Relocation_CopyCompanyData {

	/** @var float|int время, на которое блокируется порт */
	protected const _PORT_LOCK_DURATION = HOUR1;

	/**
	 * Выполняет копирование данных компании с одного домино на другое.
	 * После этого шага компания еще не считается переехавшей.
	 *
	 * Возвращает зарезервированный порт на целевом домино.
	 *
	 * Здесь делаем следующее:
	 *      — отключаем рабочий демон для компании;
	 *      — подключаем сервисный демон для компании;
	 *      — снимаем дамп и копируем его на удаленный сервер;
	 *      — отключаем сервисный демон
	 *
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \returnException
	 */
	#[\JetBrains\PhpStorm\ArrayShape([0 => Struct_Db_PivotCompanyService_PortRegistry::class, 1 => Struct_Db_PivotCompanyService_PortRegistry::class])]
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $source_domino, Struct_Db_PivotCompanyService_DominoRegistry $target_domino, Struct_Db_PivotCompany_Company $company):array {

		// проверяем, что домино совпадает, мало ли
		if ($source_domino->domino_id !== $company->domino_id) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("passed incorrect domino for company");
		}

		// получаем нужные порты для работы
		[$source_service_port, $target_service_port, $target_common_port] = static::_getPorts($source_domino, $target_domino, $company);

		try {

			// отвязываем активный порт от компании на исходном домино
			Domain_Domino_Action_StopCompany::run($source_domino, $company, "StopCompany_CopyCompanyData");
		} catch (Domain_Domino_Exception_CompanyNotBound) {

			// это нормально, если не нашли порт, то компания спит
		}

		try {

			// переводим компанию на сервисный порт на исходном домино
			Domain_Domino_Action_Port_Bind::run($source_domino, $source_service_port, $company->company_id, Domain_Domino_Action_Port_Bind::POLICY_RELOCATE_COPYING);
			Gateway_Bus_DatabaseController::beginDataCopying($source_domino, $company->company_id, $target_domino->database_host);
		} catch (\Exception $e) {

			static::_onFail($source_domino, $target_domino, $source_service_port, $target_service_port, $target_common_port);
			throw $e;
		}

		// отвязываем сервисный порт от компании на исходном домино
		Domain_Domino_Action_Port_Unbind::run($source_domino, $source_service_port, "UnbindPort_CopyCompanyData");

		return [$target_service_port, $target_common_port];
	}

	/**
	 * Возвращает порты, необходимые для копирования данных.
	 *
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \returnException
	 */
	#[\JetBrains\PhpStorm\ArrayShape([0 => Struct_Db_PivotCompanyService_PortRegistry::class, 1 => Struct_Db_PivotCompanyService_PortRegistry::class, 2 => Struct_Db_PivotCompanyService_PortRegistry::class])]
	protected static function _getPorts(Struct_Db_PivotCompanyService_DominoRegistry $source_domino, Struct_Db_PivotCompanyService_DominoRegistry $target_domino, Struct_Db_PivotCompany_Company $company):array {

		// получаем обычный порт на удаленном домино, чтобы на нем поднять компанию
		// его нужно заблокировать заранее, чтобы по завершении переезда не остаться без порта
		$target_common_port = Domain_Domino_Action_Port_LockForCompany::run(
			$target_domino, $company->company_id, Domain_Domino_Entity_Port_Registry::TYPE_COMMON, static::_PORT_LOCK_DURATION
		);

		try {

			// получаем сервисный порт, с помощью него будем раскатывать дамп при переезде
			$target_service_port = Domain_Domino_Action_Port_LockForCompany::run(
				$target_domino, $company->company_id, Domain_Domino_Entity_Port_Registry::TYPE_SERVICE, static::_PORT_LOCK_DURATION
			);
		} catch (\Exception $e) {

			Domain_Domino_Action_Port_Unlock::run($source_domino, $target_common_port);
			throw $e;
		}

		try {

			// получаем сервисный порт на исходном домино, с помощью сервисного демона снимем дамп
			$source_service_port = Domain_Domino_Action_Port_LockForCompany::run(
				$source_domino, $company->company_id, Domain_Domino_Entity_Port_Registry::TYPE_SERVICE, static::_PORT_LOCK_DURATION
			);
		} catch (\Exception $e) {

			Domain_Domino_Action_Port_Unlock::run($source_domino, $target_common_port);
			Domain_Domino_Action_Port_Unlock::run($source_domino, $target_service_port);
			throw $e;
		}

		return [$source_service_port, $target_service_port, $target_common_port];
	}

	/**
	 * Если тут вдруг что-то обломается, то нужно постараться все откатить к исходному.
	 * Шансы такие себе, конечно, но попробовать стоит.
	 */
	protected static function _onFail(
		Struct_Db_PivotCompanyService_DominoRegistry $source_domino,
		Struct_Db_PivotCompanyService_DominoRegistry $target_domino,
		Struct_Db_PivotCompanyService_PortRegistry   $source_service_port,
		Struct_Db_PivotCompanyService_PortRegistry   $target_service_port,
		Struct_Db_PivotCompanyService_PortRegistry   $target_common_port,
	):void {

		try {

			// целевой порт мы еще не трогали, можно его смело откатить в исходное состояние
			// если не выйдет — инвалидируем
			Domain_Domino_Action_Port_Unlock::run($target_domino, $target_common_port);
		} catch (\Exception) {
			Domain_Domino_Action_Port_Invalidate::run($target_domino, $target_common_port->port, "error on unlock target_common_port");
		}

		// при переезде в рамках одного домино этот порт будет одинаковый
		// случай довольно странный и не имеет практического смысла, но логика работы такая
		if ($source_domino->domino_id !== $target_domino->domino_id && $target_common_port->port !== $source_service_port->port) {

			try {

				// аналогично с предыдущим действием
				Domain_Domino_Action_Port_Unlock::run($target_domino, $target_service_port);
			} catch (\Exception) {
				Domain_Domino_Action_Port_Invalidate::run($target_domino, $target_service_port->port, "error on unlock target_service_port");
			}
		}

		// а вот сервисный порт нужно инвалидировать в любом случае, и глянуть, что с ним не так пошло
		Domain_Domino_Action_Port_Invalidate::run($source_domino, $source_service_port->port, "error on unlock source_service_port");
	}
}
