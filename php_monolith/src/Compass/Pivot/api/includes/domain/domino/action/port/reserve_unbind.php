<?php

namespace Compass\Pivot;

/**
 * Действие удаления связи порта и компании.
 * !!! Не пересоздает конфигурационный файл для компании, работает только со связью.
 */
class Domain_Domino_Action_Port_ReserveUnbind
{
	/**
	 * Отвязывает компанию от порта на домино.
	 * !!! Не пересоздает конфигурационный файл для компании, работает только со связью.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \Exception
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $port, string $unbind_reason = ""): Struct_Db_PivotCompanyService_PortRegistry
	{

		console("анбиндю порт {$port->port}");
		static::_makeRemoteUnbinding($domino, $port);
		return static::_makeLocalUnbinding($domino, $port, $unbind_reason);
	}

	/**
	 * Выполняет удаление привязки порта и компании на удаленном сервере.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \Exception
	 */
	protected static function _makeLocalUnbinding(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $port, string $unbind_reason): Struct_Db_PivotCompanyService_PortRegistry
	{

		try {

			/** начало транзакции */
			$port_to_unbound = Gateway_Db_PivotCompanyService_PortRegistry::getOne($domino->domino_id, $port->port, $port->host);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("passed non-existing port {$port->port}");
		}

		try {

			$port_to_unbound = static::_update($port_to_unbound, $domino->domino_id, $unbind_reason);
		} catch (\Exception $e) {

			// если что-то пошло не так, то нужно вызвать инвалидацию порта
			Domain_Domino_Action_Port_ReserveInvalidate::run($domino, $port_to_unbound, "error on unbind update");
			throw $e;
		}

		return $port_to_unbound;
	}

	/**
	 * Выполняет удаление привязки порта и компании на удаленном сервере.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \Exception
	 */
	protected static function _makeRemoteUnbinding(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $port): void
	{

		try {

			// выполняем привязку порта на домино
			Gateway_Bus_DatabaseController::unbindPort($domino, $port->port, $port->host);
		} catch (\Exception $e) {

			// если что-то пошло не так, то нужно вызвать инвалидацию порта
			Domain_Domino_Action_Port_Invalidate::run($domino, $port, "error on unbind remote");
			throw $e;
		}
	}

	/**
	 * Обновляет данные порта.
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _update(Struct_Db_PivotCompanyService_PortRegistry $port, string $domino_id, string $unbind_reason): Struct_Db_PivotCompanyService_PortRegistry
	{

		$old_company_id = $port->company_id;

		// обновляем данные для порта
		$port->status      = Domain_Domino_Entity_Port_Registry::STATUS_VOID;
		$port->company_id  = 0;
		$port->updated_at  = time();
		$port->locked_till = 0;

		Gateway_Db_PivotCompanyService_PortRegistry::set($domino_id, $port->port, $port->host, [
			"status"      => $port->status,
			"company_id"  => $port->company_id,
			"updated_at"  => $port->updated_at,
			"locked_till" => $port->locked_till,
		]);

		// логируем изменение статуса порта
		Domain_System_Action_TestLog::do(Domain_System_Action_TestLog::UPDATE_PORT_LOG, [
			"status"        => $port->status,
			"unbind_reason" => $unbind_reason,
			"port"          => $port->port,
			"company_id"    => $old_company_id,
			"action"        => __CLASS__,
		]);

		return $port;
	}
}
