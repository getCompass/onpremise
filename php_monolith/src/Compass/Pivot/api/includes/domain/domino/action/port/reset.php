<?php

namespace Compass\Pivot;

/**
 * Сбрасывает состояние порт до void.
 * Сбрасывать нужно осторожно, чтобы не остановить компанию внезапно.
 */
class Domain_Domino_Action_Port_Reset {

	/**
	 * Сбрасывает состояние порт до void.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $port, bool $need_update_domino_counter = true):void {

		// сбрасываем порт на домино
		Gateway_Bus_DatabaseController::resetPort($domino, $port->port);

		// уменьшаем число активных портов на домино
		if ($need_update_domino_counter && $port->status === Domain_Domino_Entity_Port_Registry::STATUS_ACTIVE) {
			Domain_Domino_Action_DoActivePortCountDelta::doPortCountDelta(-1, $domino->domino_id, $port->type);
		}

		// меняем запись в базе данных
		Gateway_Db_PivotCompanyService_PortRegistry::set($domino->domino_id, $port->port, [
			"status"      => Domain_Domino_Entity_Port_Registry::STATUS_VOID,
			"locked_till" => 0,
			"company_id"  => 0,
			"updated_at"  => 0,
		]);

		// логируем изменение статуса порта
		Domain_System_Action_TestLog::do(Domain_System_Action_TestLog::UPDATE_PORT_LOG, [
			"status"     => Domain_Domino_Entity_Port_Registry::STATUS_VOID,
			"port"       => $port->port,
			"company_id" => $port->company_id,
			"action"     => __CLASS__,
		]);
	}
}
