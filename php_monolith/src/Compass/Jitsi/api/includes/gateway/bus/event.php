<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с go_event - микросервисом асинхронных задач
 */
class Gateway_Bus_Event {

	/**
	 * Создает задачу.
	 * @throws \busException
	 */
	public static function pushTask(string $type, array $data = [], string $module = "", string $group = "default", int $need_work = 0):void {

		$grpcRequest = new \EventGrpc\TaskPushRequestStruct([
			"unique_key"  => generateUUID(),
			"type"        => $type,
			"data"        => toJson($data),
			"module"      => $module === "" ? "php_" . CURRENT_MODULE : $module,
			"group"       => $group,
			"need_work"   => $need_work,
			"error_limit" => 3,
		]);

		// отправляем задачу в grpc
		[, $status] = self::_doCallGrpc("TaskPush", $grpcRequest);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// делаем grpc запрос к указанному методу с переданными данными
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("event", \EventGrpc\eventClient::class);
		return $connection->callGrpc($method_name, $request);
	}
}