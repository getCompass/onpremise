<?php

namespace Compass\Migration;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Gateway\SocketException;
use Random\RandomException;

/**
 * класс для работы с go_database_controller - микросервисом для управления демонами mysql
 */
class Gateway_Bus_DatabaseController {

	protected const _ROUTINE_STATUS_PENDING = 10; // рутина еще в работе
	protected const _ROUTINE_STATUS_DONE    = 20; // рутина успешно завершилась
	protected const _ROUTINE_STATUS_ERROR   = 30; // рутина завершилась с ошибкой

	/**
	 * Создать бэкап mysql
	 *
	 * @param int $company_id
	 *
	 * @return string
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws SocketException
	 * @throws RandomException
	 * @throws \busException
	 */
	public static function createMysqlBackup(int $company_id):string {

		$grpcRequest = new \DatabaseControllerGrpc\CreateMysqlBackupRequestStruct([
			"company_id" => $company_id,
		]);

		// отправляем задачу в grpc
		[$response, $status] = self::_doCallGrpc("CreateMysqlBackup", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		self::_waitResponseRoutine($response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + 60);

		return $response->getBackupName();
	}

	/**
	 * Накатить миграции на компании
	 *
	 * @param int $company_id
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws RandomException
	 * @throws SocketException
	 * @throws \busException
	 */
	public static function migrateUp(int $company_id):void {

		$grpcRequest = new \DatabaseControllerGrpc\MigrateRequestStruct([
			"company_id" => $company_id,
		]);

		// отправляем задачу в grpc
		[$response, $status] = self::_doCallGrpc("MigrateUp", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		self::_waitResponseRoutine($response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + 60);
	}

	/**
	 * Чистим базу компании от легаси
	 *
	 * @param int $company_id
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws RandomException
	 * @throws SocketException
	 * @throws \busException
	 */
	public static function migrateLegacyClean(int $company_id):void {

		$grpcRequest = new \DatabaseControllerGrpc\MigrateRequestStruct([
			"company_id" => $company_id,
		]);

		// отправляем задачу в grpc
		[$response, $status] = self::_doCallGrpc("MigrateLegacyClean", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		self::_waitResponseRoutine($response->getRoutineKey(), $response->getRoutine()->getStatus(), $response->getRoutine()->getMessage(), time() + 60);
	}

	/**
	 * Занять порт компанией
	 *
	 * @param int $port
	 * @param int $company_id
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws RandomException
	 * @throws \busException
	 */
	public static function bindPort(int $port, int $company_id):void {

		$grpcRequest = new \DatabaseControllerGrpc\BindPortRequestStruct([
			"port"       => $port,
			"company_id" => $company_id,
		]);

		// отправляем задачу в grpc
		[, $status] = self::_doCallGrpc("BindPort", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Занять сервисные порт компанией
	 *
	 * @param int $company_id
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws RandomException
	 * @throws \busException
	 */
	public static function bindOnServicePort(int $company_id):array {

		$grpcRequest = new \DatabaseControllerGrpc\BindOnServicePortRequestStruct([
			"company_id" => $company_id,
		]);

		// отправляем задачу в grpc
		/** @var \DatabaseControllerGrpc\BindOnServicePortResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("BindOnServicePort", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
		return [$response->getPort(), $response->getMysqlUser(), $response->getMysqlPass()];
	}

	/**
	 * Получаем порт компании
	 *
	 * @param int $company_id
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws RandomException
	 * @throws RowNotFoundException
	 * @throws \busException
	 */
	public static function getCompanyPort(int $company_id):array {

		$grpcRequest = new \DatabaseControllerGrpc\GetCompanyPortRequestStruct([
			"company_id" => $company_id,
		]);

		// отправляем задачу в grpc
		/** @var \DatabaseControllerGrpc\GetCompanyPortResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("GetCompanyPort", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 404) {
				throw new \BaseFrame\Exception\Gateway\RowNotFoundException("port not found");
			}
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
		return [$response->getPort(), $response->getMysqlUser(), $response->getMysqlPass()];
	}

	/**
	 * Отвязать от порта компанию
	 *
	 * @param int $port
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws RandomException
	 * @throws \busException
	 */
	public static function unbindPort(int $port):void {

		$grpcRequest = new \DatabaseControllerGrpc\UnbindPortRequestStruct([
			"port" => $port,
		]);

		// отправляем задачу в grpc
		[, $status] = self::_doCallGrpc("UnbindPort", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @throws \Random\RandomException
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$conf                        = [
			"host"           => GO_DATABASE_CONTROLLER_HOST,
			"port"           => GO_DATABASE_CONTROLLER_PORT,
			"ca_certificate" => CA_CERTIFICATE,
			"token"          => self::_generateSignature(DOMINO_ID),
		];

		$connection = \Bus::configured(
			$conf, \DatabaseControllerGrpc\databaseControllerClient::class, "database_controller_" . DOMINO_ID);
		return $connection->callGrpc($method_name, $request);
	}

	/**
	 * Ожидает завершения удаленной рутины.
	 *
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \Random\RandomException
	 */
	protected static function _waitResponseRoutine(string $routine_key, int $status, string $message, int $deadline):void {

		while ($status === static::_ROUTINE_STATUS_PENDING || $deadline < time()) {

			$request = new \DatabaseControllerGrpc\GetRoutineStatusRequest([
				"routine_key" => $routine_key,
			]);

			/** @var \DatabaseControllerGrpc\GetRoutineStatusResponse $response */
			[$response, $response_status] = self::_doCallGrpc("GetRoutineStatus", $request);

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
		if ($status !== static::_ROUTINE_STATUS_DONE) {
			throw new \BaseFrame\Exception\Gateway\SocketException("routine deadline exceeded");
		}
	}

	/**
	 * Генерируем подпись запроса
	 *
	 * @throws \Random\RandomException
	 */
	protected static function _generateSignature(string $domino_id):string {

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