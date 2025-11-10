<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\SocketException;
use BaseFrame\Server\ServerProvider;

/**
 * Class Gateway_Bus_DatabaseController
 */
class Gateway_Bus_DatabaseController
{
	protected const _ROUTINE_STATUS_PENDING = 10; // рутина еще в работе
	protected const _ROUTINE_STATUS_DONE    = 20; // рутина успешно завершилась
	protected const _ROUTINE_STATUS_ERROR   = 30; // рутина завершилась с ошибкой

	/**
	 * Проверяем статус mysql на указанном порту.
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function getStatus(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port, string $host): string
	{

		$request = new \DatabaseControllerGrpc\GetStatusRequestStruct([
			"port" => $port,
			"host" => $host,
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
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function generateMysqlConfig(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row): void
	{

		$request = new \DatabaseControllerGrpc\GenerateMysqlConfigRequest([]);

		/** @var \DatabaseControllerGrpc\GenerateMysqlConfigResponse $response */
		[$_, $status] = self::_doCallGrpc($domino_registry_row, "GenerateMysqlConfig", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Добавить порт на домино
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function addPort(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port, string $host, int $status, int $type, int $locked_till, int $created_at, int $updated_at, int $company_id, int $locked_by_company_id, array $extra): void
	{

		$request = new \DatabaseControllerGrpc\AddPortRequestStruct([
			"port"                 => $port,
			"host"                 => $host,
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
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function invalidatePort(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port, string $host): void
	{

		$request = new \DatabaseControllerGrpc\SetPortInvalidRequestStruct([
			"port" => $port,
			"host" => $host,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[, $status] = self::_doCallGrpc($domino_registry_row, "SetPortInvalid", $request);
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
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws SocketException
	 */
	public static function bindPort(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port, string $host, int $company_id, array $policy_list): void
	{

		$request = new \DatabaseControllerGrpc\BindPortRequestStruct([
			"port"                         => $port,
			"host"                         => $host,
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
		static::_waitResponseRoutine($domino_registry_row, $response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + 60);
	}

	/**
	 * Разбиндиваем порт от компании
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function unbindPort(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, int $port, string $host): void
	{

		$request = new \DatabaseControllerGrpc\UnbindPortRequestStruct([
			"port" => $port,
			"host" => $host,
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
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws SocketException
	 */
	public static function beginDataCopying(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id, string $target_host): void
	{

		$request = new \DatabaseControllerGrpc\BeginDataCopyingRequestStruct([
			"company_id"  => $company_id,
			"target_host" => $target_host,
		]);

		/** @var \DatabaseControllerGrpc\BeginDataCopyingResponseStruct $response */
		[$response, $status] = self::_doCallGrpc($domino, "BeginDataCopying", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}

		// дожидаемся завершения рутины
		static::_waitResponseRoutine($domino, $response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + HOUR1);
	}

	/**
	 * Запускает рутину применения скопированных данных компании.
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws SocketException
	 */
	public static function beginDataApplying(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id): void
	{

		$request = new \DatabaseControllerGrpc\BeginDataApplyingRequestStruct([
			"company_id" => $company_id,
		]);

		/** @var \DatabaseControllerGrpc\BeginDataCopyingResponseStruct $response */
		[$response, $status] = self::_doCallGrpc($domino, "BeginDataApplying", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}

		// дожидаемся завершения рутины
		static::_waitResponseRoutine($domino, $response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + HOUR1);
	}

	/**
	 * Выполняет синхронизацию статуса порта на домино.
	 * Вызывается стоит, только если нужно что-то явно поменять (например почистить).
	 *
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 */
	public static function syncPortStatus(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $port, string $host, int $status, int $locked_till, int $company_id): void
	{

		$request = new \DatabaseControllerGrpc\SyncPortStatusRequestStruct([
			"port"                 => $port,
			"host"                 => $host,
			"status"               => $status,
			"locked_till"          => $locked_till,
			"company_id"           => $company_id,
			"locked_by_company_id" => 0,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[, $status] = self::_doCallGrpc($domino, "SyncPortStatus", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Выполняет сброс порта.
	 * При сбросе порта останавливается демон базы данных и порт переводится в статус void.
	 *
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 */
	public static function resetPort(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $port, string $host): void
	{

		$request = new \DatabaseControllerGrpc\ResetPortRequestStruct([
			"port" => $port,
			"host" => $host,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[, $status] = self::_doCallGrpc($domino, "ResetPort", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * Накатить миграции на компании
	 *
	 *
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 * @throws SocketException
	 * @throws \busException
	 */
	public static function migrateUp(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $company_id): void
	{

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
	 * @throws ParseFatalException
	 */
	public static function initSearch(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $space_id): void
	{

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
	 * @throws ParseFatalException
	 */
	public static function dropSearchTable(Struct_Db_PivotCompanyService_DominoRegistry $domino, int $space_id): void
	{

		$request = new \DatabaseControllerGrpc\DropSearchTableRequestStruct([
			"space_id" => $space_id,
		]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[$_, $status] = self::_doCallGrpc($domino, "DropSearchTable", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . formatArgs($status));
		}
	}

	/**
	 * обновляет деплой для баз данных пространств на домино
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function updateDeployment(Struct_Db_PivotCompanyService_DominoRegistry $domino): void
	{

		//только для on-premise сервера
		if (!ServerProvider::isOnPremise()) {
			return;
		}

		$request = new \DatabaseControllerGrpc\NullRequestStruct([]);

		/** @var \DatabaseControllerGrpc\NullResponseStruct $response */
		[$_, $status] = self::_doCallGrpc($domino, "UpdateDeployment", $request);
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
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws SocketException
	 */
	protected static function _waitResponseRoutine(Struct_Db_PivotCompanyService_DominoRegistry $domino, string $routine_key, int $status, string $message, int $deadline): void
	{

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
			throw new SocketException($message);
		}

		// если случился таймаут для рутины
		if ($status !== static::_ROUTINE_STATUS_DONE) {
			throw new SocketException("routine deadline exceeded");
		}
	}

	/**
	 * Делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @param mixed $request
	 *
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 */
	protected static function _doCallGrpc(Struct_Db_PivotCompanyService_DominoRegistry $domino_registry_row, string $method_name, \Google\Protobuf\Internal\Message $request): array
	{

		$go_database_controller_host = Domain_Domino_Entity_Registry_Extra::getGoDatabaseControllerHost($domino_registry_row->extra);
		$conf                        = [
			"host"           => $go_database_controller_host !== "" ? $go_database_controller_host : $domino_registry_row->database_host,
			"port"           => Domain_Domino_Entity_Registry_Extra::getGoDatabaseControllerPort($domino_registry_row->extra),
			"ca_certificate" => CA_CERTIFICATE,
			"token"          => self::_generateSignature($domino_registry_row->domino_id),
		];

		$connection = \Bus::configured(
			$conf,
			\DatabaseControllerGrpc\databaseControllerClient::class,
			"database_controller_" . $domino_registry_row->domino_id
		);
		return $connection->callGrpc($method_name, $request);
	}

	/**
	 * Генерируем подпись запроса
	 *
	 *
	 * @throws
	 */
	protected static function _generateSignature(string $domino_id): string
	{

		$secret_key = getConfig("DOMINO_ENTRYPOINT")[$domino_id]["domino_secret_key"];
		$secret_key = base64_decode($secret_key);

		$signature_arr = [
			"requested_at" => time(),
		];

		$signature_json = toJson($signature_arr);

		// варим рандомный iv и приклеиваем в начало строки
		// рандомный iv нужен, чтобы избежать возможности выявить какие-либо последовательности в шифровании
		// его можно передать вместе с зашифрованным текстом
		// длина IV ровно 16 байтов!
		$iv         = random_bytes(16);
		$ciphertext = $iv . openssl_encrypt($signature_json, ENCRYPT_CIPHER_METHOD, $secret_key, OPENSSL_RAW_DATA, $iv);

		return base64_encode($ciphertext);
	}
}
