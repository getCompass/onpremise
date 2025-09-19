<?php

namespace Compass\FileBalancer;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Class Gateway_Bus_SenderBalancer
 */
class Gateway_Bus_SenderBalancer
{
	protected const _WS_CHANNEL = "pivot"; // канал всок для пивота

	/**
	 * отправляем ws при обновлении статуса файла
	 *
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function fileStatusUpdated(int $user_id, array $prepared_file_row): void
	{

		self::_sendEvent([
			Gateway_Bus_Sender_Event_FileStatusUpdated_V1::makeEvent($prepared_file_row),
		], [$user_id]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * отправляем событие
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	protected static function _sendEvent(array $event_version_list, array $user_id_list, array $ws_users = [], array $push_data = [], int $is_need_push = 0): void
	{

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
	 * проверяем параметры
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 *
	 * @throws ParseFatalException
	 */
	protected static function _assertSendEventParameters(array $event_version_list): void
	{

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
	 *
	 * @throws \parseException
	 */
	protected static function _sendEventRequest(array $user_id_list, string $event, array $event_version_list, array $ws_users = [], array $push_data = [], int $is_need_push = 0): void
	{

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
			"channel"            => (string) self::_WS_CHANNEL,
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
	 *
	 * @return int[]
	 */
	public static function makeTalkingUserItem(int $user_id): array
	{

		return [
			"user_id" => $user_id,
		];
	}

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request): array
	{

		$connection = ShardingGateway::rpc("sender_balancer", \SenderBalancerGrpc\go_sender_balancerClient::class);

		return $connection->callGrpc($method_name, $request);
	}

	/**
	 * сгенерировать токен
	 */
	protected static function _generateToken(): string
	{

		// nosemgrep
		return self::_WS_CHANNEL . ":" . sha1(uniqid() . time());
	}
}
