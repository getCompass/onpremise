<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Class Gateway_Bus_DatabaseController
 */
class Gateway_Bus_DatabaseController {

	protected const _ROUTINE_STATUS_PENDING = 10; // рутина еще в работе
	protected const _ROUTINE_STATUS_DONE    = 20; // рутина успешно завершилась
	protected const _ROUTINE_STATUS_ERROR   = 30; // рутина завершилась с ошибкой

	/**
	 * Проверяем статус mysql на указанном порту.
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row
	 * @param int                                          $port
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	public static function getStatus(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port):string {

		$request = new \DatabaseControllerGrpc\GetStatusRequestStruct([
			"port" => $port,
		]);

		/** @var \DatabaseControllerGrpc\GetStatusResponseStruct $response */
		[$response, $status] = self::_doCallGrpc($domino_registry_row, "Report", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}

		return $response->getStatus();
	}

	/**
	 * Сгенерировать mysql конфиг для домино
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	public static function generateMysqlConfig(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row):void {

		$request = new \DatabaseControllerGrpc\GenerateMysqlConfigRequest([]);

		/** @var \DatabaseControllerGrpc\GenerateMysqlConfigResponse $response */
		[$_, $status] = self::_doCallGrpc($domino_registry_row, "GenerateMysqlConfig", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Добавить порт в домино
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row
	 * @param int                                          $port
	 * @param int                                          $status
	 * @param int                                          $type
	 * @param int                                          $locked_till
	 * @param int                                          $created_at
	 * @param int                                          $updated_at
	 * @param int                                          $company_id
	 * @param array                                        $extra
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	public static function addPort(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port, int $status, int $type, int $locked_till, int $created_at, int $updated_at, int $company_id, int $locked_by_company_id, array $extra):void {

		$request = new \DatabaseControllerGrpc\AddPortRequestStruct([
			"port"                 => $port,
			"status"               => $status,
			"type"                 => $type,
			"locked_till"          => $locked_till,
			"created_at"           => $created_at,
			"updated_at"           => $updated_at,
			"company_id"           => $company_id,
			"locked_by_company_id" => $locked_by_company_id,
			"extra"                => toJson($extra),
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[$_, $status] = self::_doCallGrpc($domino_registry_row, "AddPort", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Помечаем порт не валидным
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row
	 * @param int                                          $port
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	public static function invalidatePort(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port):void {

		$request = new \DatabaseControllerGrpc\SetPortInvalidRequestStruct([
			"port" => $port,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[, $status] = self::_doCallGrpc($domino_registry_row, "SetPortInvalid", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Занять порты на доминошке
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row
	 * @param int                                          $company_id
	 * @param int                                          $port
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	public static function createVacantCompany(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $company_id, int $port):void {

		$request = new \DatabaseControllerGrpc\CreateVacantCompanyRequestStruct([
			"company_id" => $company_id,
			"port"       => $port,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[$_, $status] = self::_doCallGrpc($domino_registry_row, "CreateVacantCompany", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Биндим порт к компании.
	 * Политики определяют, как мы будем его прикручивать.
	 *
	 * Напрямую политики передавать не стоит,
	 * лучше использовать готовые пресеты в action привязки.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	public static function bindPort(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port, int $company_id, array $policy_list):void {

		$request = new \DatabaseControllerGrpc\BindPortRequestStruct([
			"port"                         => $port,
			"company_id"                   => $company_id,
			"duplicate_data_dir_policy"    => $policy_list["duplicate_data_dir_policy"],
			"non_existing_data_dir_policy" => $policy_list["non_existing_data_dir_policy"],
		]);

		/** @var \DatabaseControllerGrpc\BindPortResponseStruct $response */
		[$response, $status] = self::_doCallGrpc($domino_registry_row, "BindPort", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}

		// дожидаемся завершения рутины
		static::_waitResponseRoutine($domino_registry_row, $response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + 180);
	}

	/**
	 * Разбиндиваем порт от компании
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row
	 * @param int                                          $port
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	public static function unbindPort(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port):void {

		$request = new \DatabaseControllerGrpc\UnbindPortRequestStruct([
			"port" => $port,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[$_, $status] = self::_doCallGrpc($domino_registry_row, "UnbindPort", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Запускает рутину копирования данных компании.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 */
	public static function beginDataCopying(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id, string $target_host):void {

		$request = new \DatabaseControllerGrpc\BeginDataCopyingRequestStruct([
			"company_id"  => $company_id,
			"target_host" => $target_host,
		]);

		/** @var \DatabaseControllerGrpc\BeginDataCopyingResponseStruct $response */
		[$response, $status] = self::_doCallGrpc($domino, "BeginDataCopying", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \BaseFrame\Exception\Gateway\BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}

		// дожидаемся завершения рутины
		static::_waitResponseRoutine($domino, $response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + HOUR1);
	}

	/**
	 * Запускает рутину применения скопированных данных компании.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 */
	public static function beginDataApplying(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id):void {

		$request = new \DatabaseControllerGrpc\BeginDataApplyingRequestStruct([
			"company_id" => $company_id,
		]);

		/** @var \DatabaseControllerGrpc\BeginDataCopyingResponseStruct $response */
		[$response, $status] = self::_doCallGrpc($domino, "BeginDataApplying", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \BaseFrame\Exception\Gateway\BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}

		// дожидаемся завершения рутины
		static::_waitResponseRoutine($domino, $response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + HOUR1);
	}

	/**
	 * Выполняет синхронизацию статуса порта на домино.
	 * Вызывается стоит, только если нужно что-то явно поменять (например почистить).
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function syncPortStatus(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $port, int $status, int $locked_till, int $company_id):void {

		$request = new \DatabaseControllerGrpc\SyncPortStatusRequestStruct([
			"port"                 => $port,
			"status"               => $status,
			"locked_till"          => $locked_till,
			"company_id"           => $company_id,
			"locked_by_company_id" => 0,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[, $status] = self::_doCallGrpc($domino, "SyncPortStatus", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \BaseFrame\Exception\Gateway\BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Выполняет сброс порта.
	 * При сбросе порта останавливается демон базы данных и порт переводится в статус void.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function resetPort(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $port):void {

		$request = new \DatabaseControllerGrpc\ResetPortRequestStruct([
			"port" => $port,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[, $status] = self::_doCallGrpc($domino, "ResetPort", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \BaseFrame\Exception\Gateway\BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Накатить миграции на компании
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino
	 * @param int                                          $company_id
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 */
	public static function migrateUp(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id):void {

		$grpcRequest = new \DatabaseControllerGrpc\MigrateRequestStruct([
			"company_id" => $company_id,
		]);

		// отправляем задачу в grpc
		[$response, $status] = self::_doCallGrpc($domino, "MigrateUp", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		self::_waitResponseRoutine($domino, $response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + 1800);
	}

	/**
	 * инициализируем поиск для пространства
	 *
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function initSearch(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $space_id):void {

		$request = new \DatabaseControllerGrpc\InitSearchRequestStruct([
			"space_id" => $space_id,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[$_, $status] = self::_doCallGrpc($domino, "InitSearch", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * удаляет поисковый индекс пространства
	 *
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function dropSearchTable(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $space_id):void {

		$request = new \DatabaseControllerGrpc\DropSearchTableRequestStruct([
			"space_id" => $space_id,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[$_, $status] = self::_doCallGrpc($domino, "DropSearchTable", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Ожидает завершения удаленной рутины.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 */
	protected static function _waitResponseRoutine(Struct_Db_PivotCompanyService_DominoRegistry $domino, string $routine_key, int $status, string $message, int $deadline):void {

		while ($status === static::_ROUTINE_STATUS_PENDING || $deadline < time()) {

			$request = new \DatabaseControllerGrpc\GetRoutineStatusRequest([
				"routine_key" => $routine_key,
			]);

			/** @var \DatabaseControllerGrpc\GetRoutineStatusResponse $response */
			[$response, $response_status] = self::_doCallGrpc($domino, "GetRoutineStatus", $request);

			if ($response_status->code !== \Grpc\STATUS_OK) {
				throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
			}

			$status  = $response->getStatus();
			$message = $response->getMessage();
		}

		// если рутина закончилась ошибкой
		if ($status === static::_ROUTINE_STATUS_ERROR) {
			throw new \BaseFrame\Exception\Gateway\SocketException($message);
		}

		// если случился таймаут для рутины
		if ($status === static::_ROUTINE_STATUS_PENDING) {
			throw new \BaseFrame\Exception\Gateway\SocketException("routine deadline exceeded");
		}
	}

	/**
	 * Делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row
	 * @param string                                       $method_name
	 * @param mixed                                        $request
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	protected static function _doCallGrpc(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$conf       = [
			"host" => $domino_registry_row->database_host,
			"port" => Domain_Domino_Entity_Registry_Extra::getGoDatabaseControllerPort($domino_registry_row->extra),
		];

		$connection = \Bus::configured($conf, \DatabaseControllerGrpc\databaseControllerClient::class, "database_controller_" . $domino_registry_row->domino_id);
		return $connection->callGrpc($method_name, $request);
	}
}