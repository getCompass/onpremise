<?php

namespace Compass\Premise;

use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы с go_sender - микросервисом для общения с клиентами по websocket
 * PHP может слать запросы к go_sender указывая массив пользователей которым необходимо разослать эвенты
 * либо отправит push-уведомление если пользователя нет онлайн (и PHP попросил это сделать)
 */
class Gateway_Bus_SenderBalancer {

	/**
	 * Отправка WS на изменение списка прав
	 *
	 * @param int $user_id
	 * @param int $permissions
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function permissionsChanged(int $user_id, int $permissions):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_PermissionsChanged_V1::makeEvent($user_id, $permissions),
		], [$user_id]);
	}

	/**
	 * Отправка WS на активацию сервера
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function serverActivated():void {

		self::_broadcastEvent([
			Gateway_Bus_Sender_Event_ServerActivated_V1::makeEvent(),
		]);
	}
	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Отправляем событие
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 * @param array                         $user_id_list
	 * @param array                         $ws_users
	 * @param array                         $push_data
	 * @param int                           $is_need_push
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	protected static function _sendEvent(array $event_version_list, array $user_id_list, array $ws_users = [], array $push_data = [], int $is_need_push = 0):void {

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

		self::_sendEventRequest($user_id_list, $event_name, $converted_event_version_list, $ws_users, $push_data, $is_need_push);
	}

	/**
	 * Отправляем событие всем
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 * @param array                         $ws_users
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	protected static function _broadcastEvent(array $event_version_list, array $ws_users = []):void {

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

		self::_sendBroadcastRequest($event_name, $converted_event_version_list, $ws_users);
	}

	/**
	 * Проверяем параметры
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
	 * Отправка события в go_sender_balancer
	 *
	 * @param array  $user_id_list
	 * @param string $event
	 * @param array  $event_version_list
	 * @param array  $ws_users
	 * @param array  $push_data
	 * @param int    $is_need_push
	 *
	 * @throws \parseException
	 */
	protected static function _sendEventRequest(array $user_id_list, string $event, array $event_version_list, array $ws_users = [], array $push_data = [], int $is_need_push = 0):void {

		// формируем массив для отправки
		$user_list = [];
		foreach ($user_id_list as $user_id) {
			$user_list[] = self::makeTalkingUserItem($user_id);
		}

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "talking.sendEvent",
			"user_list"          => (array) $user_list,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"is_need_push"       => (int) $is_need_push,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_users,
		];

		// подготавливаем event_data (шифруем map -> key)
		$params = Type_Pack_Main::replaceMapWithKeys($params);

		// проводим тест безопасности, что в ответе нет map
		Type_Pack_Main::doSecurityTest($params);

		// отправляем задачу в rabbitMq
		ShardingGateway::rabbit()->sendMessage(GO_SENDER_BALANCER_QUEUE, $params);
	}

	/**
	 * Отправка события всем в go_sender_balancer
	 *
	 * @param string $event
	 * @param array  $event_version_list
	 * @param array  $ws_users
	 *
	 * @throws \parseException
	 */
	protected static function _sendBroadcastRequest(string $event, array $event_version_list, array $ws_users = []):void {

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "talking.broadcastEvent",
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_users,
		];

		// подготавливаем event_data (шифруем map -> key)
		$params = Type_Pack_Main::replaceMapWithKeys($params);

		// проводим тест безопасности, что в ответе нет map
		Type_Pack_Main::doSecurityTest($params);

		// отправляем задачу в rabbitMq
		ShardingGateway::rabbit()->sendMessage(GO_SENDER_BALANCER_QUEUE, $params);
	}

	/**
	 * Формируем объект talking_user_item
	 *
	 * @param int $user_id
	 *
	 * @return int[]
	 */
	public static function makeTalkingUserItem(int $user_id):array {

		return [
			"user_id" => $user_id,
		];
	}

	/**
	 * Делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @param string                            $method_name
	 * @param \Google\Protobuf\Internal\Message $request
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("sender_balancer", \SenderBalancerGrpc\go_sender_balancerClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}