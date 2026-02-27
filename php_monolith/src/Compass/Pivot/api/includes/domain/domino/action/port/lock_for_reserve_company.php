<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * Действие привязки порта к компании при поднятии резервной компании.
 * Выполняет блокирование порта для дальнейшей передачи компании.
 */
class Domain_Domino_Action_Port_LockForReserveCompany
{
	/**
	 * Выбирает свободный порт указанного типа.
	 * Блокирует этот порт для дальнейших действий.
	 *
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 */
	public static function run(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id, int $lock_duration, int $port, string $host): Struct_Db_PivotCompanyService_PortRegistry
	{

		// получаем уже заблокированный обновленный порт
		$void_port = static::_getLocked($domino, $company_id, $port, $host, $lock_duration);

		try {

			// синхронизируем статус порта на домино
			Gateway_Bus_DatabaseController::syncPortStatus(
				$domino,
				$void_port->port,
				$void_port->host,
				$void_port->status,
				$void_port->locked_till,
				$void_port->company_id
			);
		} catch (\Exception $e) {

			Domain_Domino_Action_Port_ReserveInvalidate::run($domino, $void_port, "error on lock");
			throw $e;
		}

		return $void_port;
	}

	/**
	 * Получает свободный порт и блокирует его под компанию.
	 * Обновляет данные для порта на pivot-сервере.
	 *
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws ParseFatalException
	 */
	protected static function _getLocked(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id, int $port, string $host, int $lock_duration): Struct_Db_PivotCompanyService_PortRegistry
	{

		try {
			return static::_getExisting($domino, $port, $host);
		} catch (RowNotFoundException) {
			// если не нашлось, то ничего страшного
		}

		try {

			// получаем один порт указанного типа
			$void_port = Gateway_Db_PivotCompanyService_PortRegistry::getOne($domino->domino_id, $port, $host);
		} catch (RowNotFoundException) {
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

		console("лочу порт {$void_port->port} для компании {$company_id}");
		return $void_port;
	}

	/**
	 * Пытается получить уже существующий порт.
	 *
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 */
	protected static function _getExisting(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $port, string $host): Struct_Db_PivotCompanyService_PortRegistry
	{

		try {

			$existing_port = Gateway_Db_PivotCompanyService_PortRegistry::getOne($domino->domino_id, $port, $host);
		} catch (RowNotFoundException $e) {

			// если зарезервированного порта для компании не нашлось
			Gateway_Db_PivotCompanyService_PortRegistry::rollback();
			throw $e;
		}

		$existing_port->updated_at = time();

		// обновляем запись для порта, устанавливая время блокировки
		Gateway_Db_PivotCompanyService_PortRegistry::set($domino->domino_id, $existing_port->port, $existing_port->host, [
			"updated_at" => $existing_port->updated_at,
		]);

		return $existing_port;
	}
}
