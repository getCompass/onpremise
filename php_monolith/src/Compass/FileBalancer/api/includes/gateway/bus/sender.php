<?php

declare(strict_types=1);

namespace Compass\FileBalancer;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use CompassApp\Company\CompanyProvider;

/**
 * класс для работы с go_sender - микросервисом для общения с клиентами по websocket
 * PHP может слать запросы к go_sender указывая массив пользователей которым необходимо разослать эвенты
 * либо отправит push-уведомление если пользователя нет онлайн (и PHP попросил это сделать)
 */
class Gateway_Bus_Sender
{
	protected const _WS_CHANNEL_PIVOT  = "pivot"; // канал всок для пивота
	protected const _WS_CHANNEL_DOMINO = "domino"; // канал всок для домино

	/**
	 * отправляем ws при прослушивании голосовухи
	 *
	 * @throws busException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function doFileVoiceListen(int $user_id, string $file_map): void
	{

		// формируем список пользователей на отправку ws
		$user_list[] = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_FileVoiceListen_V1::makeEvent($file_map),
		], $user_list);
	}

	/**
	 * отправляем ws при обновлении статуса файла
	 *
	 * @throws busException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function fileStatusUpdated(int $user_id, array $prepared_file_row): void
	{

		// формируем список пользователей на отправку ws
		$user_list[] = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_FileStatusUpdated_V1::makeEvent($prepared_file_row),
		], $user_list);
	}
	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * отправляем событие
	 *
	 * @param Struct_Sender_Event[] $event_version_list
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws busException
	 * @throws ParseFatalException
	 */
	protected static function _sendEvent(array $event_version_list, array $user_list, array $push_data = [], array $ws_users = [], string $routine_key = ""): void
	{

		// проверяем что прислали корректные параметры
		self::_assertSendEventParameters($event_version_list);

		// если прислали пустой массив получателей
		if (count($user_list) < 1) {

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

		self::_sendEventRequest($event_name, $user_list, $converted_event_version_list, $ws_users, $push_data, $routine_key);
	}

	/**
	 * проверяем параметры
	 *
	 * @param Struct_Sender_Event[] $event_version_list
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
	 * Отправить событие в go_sender
	 *
	 * @long большие структуры
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	protected static function _sendEventRequest(string $event, array $user_list, array $event_version_list, array $ws_user_list = [], array $push_data = [], string $routine_key = ""): void
	{

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "sender.sendEvent",
			"user_list"          => (array) $user_list,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (object) $ws_user_list,
			"routine_key"        => (string) $routine_key,
			"channel"            => COMPANY_ID === 0 ? self::_WS_CHANNEL_PIVOT : self::_WS_CHANNEL_DOMINO,
		];

		// подготавливаем event_data (шифруем map -> key)
		$params     = Type_Pack_Main::replaceMapWithKeys($params);
		$class_name = CompanyProvider::id() === 0 ? \SenderGrpc\SenderSendEventRequestStruct::class : \Company\SenderGrpc\SenderSendEventRequestStruct::class;

		$converted_user_list          = self::_convertReceiverUserListToGrpcStructure($user_list);
		$converted_event_version_list = self::_convertEventVersionListToGrpcStructure($params["event_version_list"]);
		$grpc_request                 = new $class_name([
			"user_list"          => $converted_user_list,
			"event"              => $params["event"],
			"event_version_list" => $converted_event_version_list,
			"push_data"          => toJson($params["push_data"]),
			"uuid"               => $params["uuid"],
			"ws_users"           => isset($params["ws_users"]) ? toJson($params["ws_users"]) : "",
			"company_id"         => COMPANY_ID,
			"channel"            => COMPANY_ID === 0 ? self::_WS_CHANNEL_PIVOT : self::_WS_CHANNEL_DOMINO,
		]);

		/** @noinspection PhpParamsInspection $grpc_request что ты такое */
		[, $status] = self::_doCallGrpc("SenderSendEvent", $grpc_request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * конвертируем user_list в структуру понятную grpc
	 */
	protected static function _convertReceiverUserListToGrpcStructure(array $user_list): array
	{

		$output = [];

		$class_name = CompanyProvider::id() == 0 ? \SenderGrpc\EventUserStruct::class : \Company\SenderGrpc\EventUserStruct::class;
		foreach ($user_list as $user_item) {

			$output[] = new $class_name([
				"user_id"   => $user_item["user_id"],
				"need_push" => $user_item["need_push"],
			]);
		}

		return $output;
	}

	/**
	 * конвертируем event_version_list в структуру понятную grpc
	 */
	protected static function _convertEventVersionListToGrpcStructure(array $event_version_list): array
	{

		$output = [];

		$class_name = CompanyProvider::id() === 0 ? \SenderGrpc\EventVersionItem::class : \Company\SenderGrpc\EventVersionItem::class;
		foreach ($event_version_list as $event_version_item) {

			$output[] = new $class_name([
				"version" => (int) $event_version_item["version"],
				"data"    => toJson((object) $event_version_item["data"]),
			]);
		}

		return $output;
	}

	// -------------------------------------------------------
	// WS_USERS
	// -------------------------------------------------------

	/**
	 * Формируем объект talking_user_item
	 *
	 * @return int[]
	 */
	public static function makeTalkingUserItem(int $user_id, bool $is_need_push): array
	{

		return [
			"user_id"   => $user_id,
			"need_push" => $is_need_push ? 1 : 0,
		];
	}

	/**
	 * Выполняем grpc запрос
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @noinspection PhpUndefinedNamespaceInspection \Google\Protobuf\Internal\Message что ты такое
	 * @noinspection PhpUndefinedClassInspection \Google\Protobuf\Internal\Message что ты такое
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request): array
	{

		$sender_client_class = CompanyProvider::id() === 0 ? \SenderGrpc\senderClient::class : \Company\SenderGrpc\senderClient::class;
		$connection          = ShardingGateway::rpc("sender", $sender_client_class);

		return $connection->callGrpc($method_name, $request);
	}
}
