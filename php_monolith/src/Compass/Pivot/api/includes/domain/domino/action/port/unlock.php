<?php

namespace Compass\Pivot;

/**
 * Убирает блокировку порта, чтобы его можно было выдать под другие компании.
 */
class Domain_Domino_Action_Port_Unlock {

	/**
	 * Снимает блокировку с порта и синхронизирует действие с домино.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $port):Struct_Db_PivotCompanyService_PortRegistry {

		$old_company_id = $port->company_id;

		$port->status      = Domain_Domino_Entity_Port_Registry::STATUS_VOID;
		$port->company_id  = 0;
		$port->locked_till = 0;
		$port->updated_at  = time();

		// обновляем запись для порта, сбрасывая данные блокировки
		Gateway_Db_PivotCompanyService_PortRegistry::set($domino->domino_id, $port->port, [
			"status"      => $port->status,
			"company_id"  => $port->company_id,
			"locked_till" => $port->locked_till,
			"updated_at"  => $port->updated_at,
		]);

		// синхронизируем статус порта на домино
		Gateway_Bus_DatabaseController::syncPortStatus($domino, $port->port, $port->status, $port->locked_till, $port->company_id);

		// логируем изменение статуса порта
		Domain_System_Action_TestLog::do(Domain_System_Action_TestLog::UPDATE_PORT_LOG, [
			"status"     => $port->status,
			"port"       => $port->port,
			"company_id" => $old_company_id,
			"action"     => __CLASS__,
		]);

		return $port;
	}
}