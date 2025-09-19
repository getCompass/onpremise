<?php

namespace Compass\Pivot;

/**
 * Действие привязки порта к компании при поднятии резервной компании.
 * Выполняет блокирование порта для дальнейшей передачи компании.
 */
class Domain_Domino_Action_Port_LockForReserveCompany {

	/**
	 * Выбирает свободный порт указанного типа.
	 * Блокирует этот порт для дальнейших действий.
	 *
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \returnException
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id, int $lock_duration, int $port, string $host):Struct_Db_PivotCompanyService_PortRegistry {

		// получаем уже заблокированный обновленный порт
		$void_port = static::_getLocked($domino, $company_id, $port, $host, $lock_duration);

		try {

			// синхронизируем статус порта на домино
			Gateway_Bus_DatabaseController::syncPortStatus(
				$domino, $void_port->port, $void_port->host, $void_port->status, $void_port->locked_till, $void_port->company_id
			);
		} catch (\Exception $e) {

			Domain_Domino_Action_Port_Invalidate::run($domino, $void_port, "error on lock");
			throw $e;
		}

		return $void_port;
	}

	/**
	 * Получает свободный порт и блокирует его под компанию.
	 * Обновляет данные для порта на pivot-сервере.
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \returnException
	 *
	 * @long много обработок ошибок :(
	 */
	protected static function _getLocked(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id, int $port, string $host, int $lock_duration):Struct_Db_PivotCompanyService_PortRegistry {

		try {

			return static::_getExisting($domino, $company_id, $port, $host);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// если не нашлось, то ничего страшного
		}

		/** начало транзакции */
		Gateway_Db_PivotCompanyService_PortRegistry::beginTransaction();

		try {

			// получаем один порт указанного типа для обновления
			$void_port = Gateway_Db_PivotCompanyService_PortRegistry::getForUpdate($domino->domino_id, $port, $host);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Db_PivotCompanyService_PortRegistry::rollback();
			throw new Domain_Domino_Exception_VoidPortsExhausted("there is no void port with port {$port} and host {$host}");
		}

		$void_port->status      = Domain_Domino_Entity_Port_Registry::STATUS_LOCKED;
		$void_port->company_id  = $company_id;
		$void_port->locked_till = time() + $lock_duration;
		$void_port->updated_at  = time();

		// обновляем запись для порта, устанавливая время блокировки
		Gateway_Db_PivotCompanyService_PortRegistry::set($domino->domino_id, $void_port->port, $void_port->host, [
			"status"      => $void_port->status,
			"company_id"  => $void_port->company_id,
			"locked_till" => $void_port->locked_till,
			"updated_at"  => $void_port->updated_at,
		]);

		Gateway_Db_PivotCompanyService_PortRegistry::commitTransaction();
		/** конец транзакции */

		// логируем изменение статуса порта
		Domain_System_Action_TestLog::do(Domain_System_Action_TestLog::UPDATE_PORT_LOG, [
			"status"     => $void_port->status,
			"port"       => $void_port->port,
			"company_id" => $void_port->company_id,
			"action"     => __CLASS__,
		]);

		console("лочу порт {$void_port->port} для компании {$company_id}");
		return $void_port;
	}

	/**
	 * Пытается получить уже существующий порт.
	 *
	 * @return Struct_Db_PivotCompanyService_PortRegistry
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \returnException
	 */
	protected static function _getExisting(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id, int $port, string $host):Struct_Db_PivotCompanyService_PortRegistry {

		/** начало транзакции */
		Gateway_Db_PivotCompanyService_PortRegistry::beginTransaction();

		try {

			$existing_port = Gateway_Db_PivotCompanyService_PortRegistry::getForUpdate($domino->domino_id, $port, $host);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException $e) {

			// если зарезервированного порта для компании не нашлось
			Gateway_Db_PivotCompanyService_PortRegistry::rollback();
			throw $e;
		}

		$existing_port->updated_at = time();

		// обновляем запись для порта, устанавливая время блокировки
		Gateway_Db_PivotCompanyService_PortRegistry::set($domino->domino_id, $existing_port->port, $existing_port->host, [
			"updated_at" => $existing_port->updated_at,
		]);

		Gateway_Db_PivotCompanyService_PortRegistry::commitTransaction();
		/** конец транзакции */

		return $existing_port;
	}
}