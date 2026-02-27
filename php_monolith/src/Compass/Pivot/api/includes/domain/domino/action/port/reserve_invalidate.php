<?php

namespace Compass\Pivot;

/**
 * Действие пометки порта как порта с ошибкой.
 */
class Domain_Domino_Action_Port_ReserveInvalidate
{
	/**
	 * Отмечает порт как ошибочный.
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry $port, string $invalidate_reason = ""): void
	{

		try {

			// получаем один порт указанного типа
			Gateway_Db_PivotCompanyService_PortRegistry::getOne($domino->domino_id, $port->port, $port->host);

			// обновляем запись для порта, устанавливая время блокировки
			Gateway_Db_PivotCompanyService_PortRegistry::set($domino->domino_id, $port->port, $port->host, [
				"status"     => Domain_Domino_Entity_Port_Registry::STATUS_INVALID,
				"updated_at" => time(),
			]);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			// такого не должно случаться
		}

		try {

			// инвалидируем порт на домино
			Gateway_Bus_DatabaseController::invalidatePort($domino, $port->port, $port->host);
		} catch (\Exception) {
			// подавляем любое исключение, умирать тут никак нельзя
		}
	}
}
