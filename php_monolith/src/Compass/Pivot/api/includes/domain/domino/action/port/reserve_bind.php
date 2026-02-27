<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\SocketException;

/**
 * Действие привязки порта домино к компании на резервном сервере.
 * !!! Не пересоздает конфигурационный файл для компании, оно лишь связывает порт и компанию.
 */
class Domain_Domino_Action_Port_ReserveBind
{
	/**
	 * Выполняет привязку компании к порту на домино.
	 * Возвращает обновленный порт.
	 *
	 * !!! Не пересоздает конфигурационный файл для компании, оно лишь связывает порт и компанию.
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws SocketException
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $port, int $company_id, array $policy_list): void
	{

		console("начинаем привязку порта {$port->port} для компании {$company_id}");
		Type_System_Admin::log("start_company_process", "Выполнили привязку порта и компании {$company_id} на локальном сервере");
		console("выполнили привязку порта и компании {$company_id} на локальном сервере");

		$go_database_controller_host = Domain_Domino_Entity_Registry_Extra::getGoDatabaseControllerHost($domino->extra);
		$go_database_controller_host = $go_database_controller_host !== "" ? $go_database_controller_host : $domino->database_host;
		$go_database_controller_port = Domain_Domino_Entity_Registry_Extra::getGoDatabaseControllerPort($domino->extra);

		Type_System_Admin::log("start_company_process", "Привязываем компанию к порту в микросервисе go_database: host {$go_database_controller_host}, port {$go_database_controller_port}");

		// выполняем привязку порта на домино и сразу накатываем миграции
		// если что-то пошло не так, то на резервном не нужно вызывать инвалидацию порта
		Gateway_Bus_DatabaseController::bindPort($domino, $port->port, $port->host, $company_id, $policy_list);

		console("выполнили привязку порта и компании {$company_id} на удаленном сервере");
		Type_System_Admin::log("start_company_process", "Выполнили привязку порта и компании {$company_id} на удаленном сервере");
	}
}
