<?php

namespace Compass\Speaker;

/**
 * класс для работы с go_event - микросервисом асинхронных задач
 */
class Gateway_Bus_Event {

	/**
	 * Создает задачу.
	 * @throws \busException
	 */
	public static function pushTask(string $task_name, array $data = [], string $module = "", string $group = "default", int $need_work = 0):void {

		$grpcRequest = new \EventGrpc\TaskPushRequestStruct([
			"unique_key"  => generateUUID(),
			"type"        => $task_name,
			"data"        => toJson($data),
			"module"      => $module === "" ? "php_" . CURRENT_MODULE : $module,
			"group"       => $group,
			"need_work"   => $need_work,
			"error_limit" => 3,
			"company_id"  => COMPANY_ID,
		]);

		// отправляем задачу в grpc
		[, $status] = self::_doCallGrpc("TaskPush", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Устанавливает ловушку для событий.
	 *
	 * @param string $unique_key
	 * @param string $subscriber
	 * @param string $event_type
	 * @param int    $created_after
	 * @param array  $filter_list
	 *
	 * @throws \busException
	 */
	public static function setEventTrap(string $unique_key, string $subscriber, string $event_type, int $created_after, array $filter_list = []):void {

		$grpcRequest = new \EventGrpc\EventSetEventTrapRequestStruct([
			"unique_key"    => $unique_key,
			"subscriber"    => $subscriber,
			"event_type"    => $event_type,
			"created_after" => $created_after,
			"translations"  => $filter_list,
			"company_id"    => COMPANY_ID,
		]);

		// отправляем задачу в grpc
		[, $status] = self::_doCallGrpc("EventSetEventTrap", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Ждет срабатывания ловушки для событий.
	 *
	 * @param string $unique_key
	 *
	 * @return bool
	 * @throws \busException
	 */
	public static function waitEventTrap(string $unique_key):bool {

		$grpcRequest = new \EventGrpc\EventWaitEventTrapRequestStruct([
			"unique_key" => $unique_key,
			"company_id" => COMPANY_ID,
		]);

		// отправляем задачу в grpc
		[$response, $status] = self::_doCallGrpc("EventWaitEventTrap", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return $response->getIsFound();
	}

	// делаем grpc запрос к указанному методу с переданными данными
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("event", \EventGrpc\eventClient::class);
		return $connection->callGrpc($method_name, $request);
	}
}