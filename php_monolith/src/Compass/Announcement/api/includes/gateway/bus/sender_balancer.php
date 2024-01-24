<?php

namespace Compass\Announcement;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс отправки данных в sender-balancer.
 */
class Gateway_Bus_SenderBalancer {

	/** @var int время за которое нужно успеть авторизоваться по полученному токену */
	protected const _TOKEN_EXPIRE_TIME = 1 * 60;

	/**
	 * Опубликован новый анонс.
	 * Рассылается для указанного списка пользователей.
	 *
	 * @param array $user_id_list
	 *
	 * @throws ParseFatalException
	 */
	public static function announcementPublished(array $user_id_list):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_AnnouncementPublished_V1::makeEvent(),
		], $user_id_list);
	}

	/**
	 * Опубликован новый анонс.
	 * Рассылается всем подключенным пользователям.
	 */
	public static function globalAnnouncementPublished():void {

		self::_sendEventBroadcast([
			Gateway_Bus_SenderBalancer_Event_AnnouncementPublished_V1::makeEvent(),
		]);
	}

	/**
	 * Создаем токен для указанного пользователя.
	 *
	 * @param int    $user_id
	 * @param string $device_id
	 * @param string $platform
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	public static function getConnection(int $user_id, string $device_id = "", string $platform = Type_Api_Platform::PLATFORM_OTHER):array {

		// генерируем token
		$token = self::_generateToken();

		// формируем массив для отправки
		$request = new \SenderBalancerGrpc\SenderBalancerSetTokenRequestStruct([
			"user_id"   => $user_id,
			"token"     => $token,
			"platform"  => $platform,
			"device_id" => $device_id,
			"expire"    => time() + self::_TOKEN_EXPIRE_TIME,
		]);

		// получаем из конфига где находится микросервис
		[$response] = self::_doCallGrpc("SenderBalancerSetToken", $request);

		// формируем ссылку для установления wss соединения
		$config = getConfig("SHARDING_GO");
		$url    = $config["sender_balancer"]["url"] . $response->getNode();

		return [$token, $url];
	}

	/**
	 * сгенерировать токен
	 *
	 * @return string
	 */
	protected static function _generateToken():string {

		return sha1(uniqid() . time());
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * отправляем событие
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 * @param array                         $user_id_list
	 * @param array                         $ws_users
	 *
	 * @throws ParseFatalException
	 */
	protected static function _sendEvent(array $event_version_list, array $user_id_list, array $ws_users = []):void {

		// проверяем что прислали корректные параметры
		self::_assertSendEventParameters($event_version_list);

		// если прислали пустой массив получателей
		if (count($user_id_list) < 1) {

			// ничего не делаем
			return;
		}

		// получаем название событие
		$event_name = $event_version_list[0]->event;

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list = [];
		foreach ($event_version_list as $event) {

			$converted_event_version_list[] = [
				"version" => (int) $event->version,
				"data"    => (object) $event->ws_data,
			];
		}

		self::_sendEventRequest($user_id_list, $event_name, $converted_event_version_list, $ws_users);
	}

	/**
	 * проверяем параметры
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 *
	 * @throws ParseFatalException
	 */
	protected static function _assertSendEventParameters(array $event_version_list):void {

		// если прислали пустой массив версий метода
		if (count($event_version_list) < 1) {
			throw new ParseFatalException("incorrect array event version list");
		}

		// проверяем, что все версии события описывают один и тот же метод
		$ws_method_name = $event_version_list[0]->event;
		foreach ($event_version_list as $event) {

			if ($event->event !== $ws_method_name) {
				throw new ParseFatalException("different ws event names");
			}
		}
	}

	/**
	 * Отправка события в go_sender_balancer для указанного списка пользователей.
	 *
	 * @param array  $user_id_list
	 * @param string $event
	 * @param array  $event_version_list
	 * @param array  $ws_users
	 */
	protected static function _sendEventRequest(array $user_id_list, string $event, array $event_version_list, array $ws_users = []):void {

		// формируем массив для отправки
		$user_list = [];
		foreach ($user_id_list as $user_id) {

			$user_list[] = [
				"user_id" => $user_id,
			];
		}

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "talking.sendEvent",
			"user_list"          => (array) $user_list,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_users,
		];

		// отправляем задачу в rabbitMq
		ShardingGateway::rabbit()->sendMessage(GO_SENDER_BALANCER_QUEUE, $params);
	}

	/**
	 * отправляем событие всем пользователям
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 * @param array                         $ws_users
	 *
	 * @throws ParseFatalException
	 */
	protected static function _sendEventBroadcast(array $event_version_list, array $ws_users = []):void {

		// проверяем что прислали корректные параметры
		self::_assertSendEventParameters($event_version_list);

		// получаем название событие
		$event_name = $event_version_list[0]->event;

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list = [];
		foreach ($event_version_list as $event) {

			$converted_event_version_list[] = [
				"version" => (int) $event->version,
				"data"    => (object) $event->ws_data,
			];
		}

		self::_broadcastEventRequest($event_name, $converted_event_version_list, $ws_users);
	}

	/**
	 * Отправка события в go_sender_balancer для все подключенных пользователей
	 *
	 * @param string $event
	 * @param array  $event_version_list
	 * @param array  $ws_users
	 */
	protected static function _broadcastEventRequest(string $event, array $event_version_list, array $ws_users = []):void {

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "talking.broadcastEvent",
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_users,
		];

		// отправляем задачу в rabbitMq
		ShardingGateway::rabbit()->sendMessage(GO_SENDER_BALANCER_QUEUE, $params);
	}

	/**
	 * Вызов GRPC метода.
	 *
	 * @param string                            $method_name
	 * @param \Google\Protobuf\Internal\Message $request
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("sender_balancer", \SenderBalancerGrpc\go_sender_balancerClient::class);

		[$response, $status] = $connection->callGrpc($method_name, $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return [$response, $status];
	}
}