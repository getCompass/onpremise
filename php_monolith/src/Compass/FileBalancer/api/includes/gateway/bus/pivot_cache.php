<?php

namespace Compass\FileBalancer;

use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с go_pivot_cache
 */
class Gateway_Bus_PivotCache {

	// -------------------------------------------------------
	// Session
	// -------------------------------------------------------

	// получить информацию по сессии
	public static function getInfo(string $session_uniq, string $shard_id, int $table_id):array {

		if (isset($GLOBALS["session_list"][$session_uniq])) {
			return $GLOBALS["session_list"][$session_uniq];
		}

		$request = new \PivotCacheGrpc\SessionGetInfoRequestStruct([
			"session_uniq" => $session_uniq,
			"shard_id"     => $shard_id,
			"table_id"     => $table_id,
		]);

		/** @var \PivotCacheGrpc\SessionGetInfoResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("SessionGetInfo", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			// если go_pivot_cache не смог получить данные из БД
			if ($status->code == 500) {
				throw new BusFatalException("database error in go_pivot_cache");
			}

			// если сессия не найдена
			if ($status->code == 902) {
				throw new \cs_SessionNotFound();
			}

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// записываем сессию в GLOBALS
		$GLOBALS["session_list"][$session_uniq] = [
			"user_id"      => $response->getUserId(),
			"refreshed_at" => $response->getRefreshedAt(),
		];

		return $GLOBALS["session_list"][$session_uniq];
	}

	// очистить кэш go_pivot_cache по session_uniq [все ноды ТЕКУЩЕГО DPC]
	public static function clearSessionCacheBySessionUniq(string $session_uniq):void {

		$ar_post = [
			"method"       => "session.deleteBySessionUniq",
			"session_uniq" => $session_uniq,
		];

		// отсылаем сообщение
		\Bus::rabbitSendToExchange("go_pivot_cache", $ar_post);
	}

	// очистить кэш go_pivot_cache по user_id [все ноды ТЕКУЩЕГО DPC]
	public static function clearSessionCacheByUserId(int $user_id):void {

		$ar_post = [
			"method"  => "session.deleteByUserId",
			"user_id" => $user_id,
		];

		// отсылаем сообщение
		\Bus::rabbitSendToExchange("go_pivot_cache", $ar_post);

		unset($GLOBALS["user_list"][$user_id]);
	}

	// очищает информацию пользователя в go_pivot_cache
	public static function clearUserCacheInfo(int $user_id):void {

		$ar_post = [
			"method"  => "session.deleteUserInfo",
			"user_id" => $user_id,
		];

		// отсылаем сообщение
		\Bus::rabbitSendToExchange("go_pivot_cache", $ar_post);

		unset($GLOBALS["user_list"][$user_id]);
	}

	// возвращает статус микросервиса
	public static function getStatus():array {

		$request             = new \PivotCacheGrpc\SystemStatusRequestStruct();
		[$response, $status] = self::_doCallGrpc("SystemStatus", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return self::_formatSystemStatusResponse($response);
	}

	// форматируем ответ system.status
	protected static function _formatSystemStatusResponse(\PivotCacheGrpc\SystemStatusResponseStruct $response):array {

		// формируем ответ
		return [
			"name"       => $response->getName(),
			"goroutines" => $response->getGoroutines(),
			"memory"     => $response->getMemory(),
			"memory_kb"  => $response->getMemoryKb(),
			"memory_mb"  => $response->getMemoryMb(),
			"uptime"     => $response->getUptime(),
		];
	}

	// -------------------------------------------------------
	// User
	// -------------------------------------------------------

	/**
	 * Получить инфу о пользователе по user_id
	 *
	 * @throws busException
	 * @throws cs_UserNotFound
	 * @throws parseException
	 * @throws userAccessException
	 */
	public static function getUserInfo(int $user_id):Struct_Db_PivotUser_User {

		if (isset($GLOBALS["user_list"][$user_id])) {
			return $GLOBALS["user_list"][$user_id];
		}

		$request = new \PivotCacheGrpc\UsersGetInfoRequestStruct([
			"user_id" => $user_id,
		]);
		[$response, $status] = self::_doCallGrpc("UserGetInfo", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 401) {
				throw new \userAccessException();
			}

			if ($status->code == 901) {
				throw new \cs_UserNotFound();
			}

			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// формируем структуру
		$user_info = self::_makeUserStruct($response);

		$GLOBALS["user_list"][$user_id] = $user_info;
		return $user_info;
	}

	/**
	 * Форматируем информацию о пользователе
	 *
	 */
	protected static function _makeUserStruct(\PivotCacheGrpc\UsersGetInfoResponseStruct $response):Struct_Db_PivotUser_User {

		// формируем ответ
		return new Struct_Db_PivotUser_User(
			$response->getUserId(),
			$response->getNpcType(),
			$response->getCreatedAt(),
			$response->getUpdatedAt(),
			$response->getCountryCode(),
			$response->getFullName(),
			$response->getShortDescription(),
			$response->getAvatarFileMap(),
			$response->getStatus(),
			fromJson($response->getExtra())
		);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @throws busException
	 * @throws parseException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("pivot_cache", \PivotCacheGrpc\pivotCacheClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}
