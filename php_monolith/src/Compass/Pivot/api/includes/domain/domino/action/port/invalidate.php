<?php

namespace Compass\Pivot;

/**
 * Действие пометки порта как порта с ошибкой.
 */
class Domain_Domino_Action_Port_Invalidate {

	/**
	 * Отмечает порт как ошибочный.
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $port, string $invalidate_reason = ""):void {

		/** начало транзакции */
		Gateway_Db_PivotCompanyService_PortRegistry::beginTransaction();

		try {

			// получаем один порт указанного типа для обновления
			Gateway_Db_PivotCompanyService_PortRegistry::getForUpdate($domino->domino_id, $port->port, $port->host);

			// обновляем запись для порта, устанавливая время блокировки
			Gateway_Db_PivotCompanyService_PortRegistry::set($domino->domino_id, $port->port, $port->host, [
				"status"     => Domain_Domino_Entity_Port_Registry::STATUS_INVALID,
				"updated_at" => time(),
			]);

			// логируем изменение статуса порта
			Domain_System_Action_TestLog::do(Domain_System_Action_TestLog::UPDATE_PORT_LOG, [
				"status" => Domain_Domino_Entity_Port_Registry::STATUS_INVALID,
				"reason" => $invalidate_reason,
				"port"   => $port,
				"action" => __CLASS__,
			]);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// такого не должно случаться
			Gateway_Db_PivotCompanyService_PortRegistry::rollback();
		}

		Gateway_Db_PivotCompanyService_PortRegistry::commitTransaction();
		/** конец транзакции */

		try {

			// инвалидируем порт на домино
			Gateway_Bus_DatabaseController::invalidatePort($domino, $port->port, $port->host);
		} catch (\Exception) {

			// подавляем любое исключение, умирать тут никак нельзя
		}
	}
}