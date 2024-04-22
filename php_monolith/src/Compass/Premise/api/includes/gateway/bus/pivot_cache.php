<?php

namespace Compass\Premise;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с go_pivot_cache
 */
class Gateway_Bus_PivotCache {

	// -------------------------------------------------------
	// Session
	// -------------------------------------------------------

	/**
	 * Получить информацию по сессии
	 *
	 * @param string $session_uniq
	 * @param string $shard_id
	 * @param int    $table_id
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_SessionNotFound
	 */
	public static function getInfo(string $session_uniq, string $shard_id, int $table_id):array {

		// если нашли сессию в GLOBALS - возвращаем
		$session = self::_getSessionFromGlobals($session_uniq);
		if ($session !== false) {
			return $session;
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

	/**
	 * Вернуть статус микросервиса
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getStatus():array {

		$request = new \PivotCacheGrpc\SystemStatusRequestStruct();
		[$response, $status] = self::_doCallGrpc("SystemStatus", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
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

	/**
	 * Попытаться получить сессию из GLOBALS
	 *
	 * @param string $session_uniq
	 *
	 * @return array|false
	 */
	protected static function _getSessionFromGlobals(string $session_uniq):array|false {

		//
		if (isset($GLOBALS["session_list"][$session_uniq])) {

			// если рефреша еще нет в GLOBALS - добавляем 0, так как никогда не обновляли куку значит
			if (is_int($GLOBALS["session_list"][$session_uniq])) {

				$GLOBALS["session_list"][$session_uniq] = [
					"user_id"      => $GLOBALS["session_list"][$session_uniq],
					"refreshed_at" => 0,
				];
			}
			return $GLOBALS["session_list"][$session_uniq];
		}

		return false;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @param string                            $method_name
	 * @param \Google\Protobuf\Internal\Message $request
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("pivot_cache", \PivotCacheGrpc\pivotCacheClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}
