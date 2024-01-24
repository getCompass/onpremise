<?php

namespace Compass\Speaker;

use BaseFrame\Exception\Domain\ParseFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * класс для работы с go_talking_handler - микросервисом для общения с клиентами по websocket
 * PHP может слать запросы к go_talking_handler указывая массив пользователей которым необходимо разослать эвенты
 * либо отправит push-уведомление если пользователя нет онлайн (и PHP попросил это сделать)
 */
class Gateway_Bus_Sender {

	// -------------------------------------------------------
	// WS события
	// -------------------------------------------------------

	// функция отправляет информацию о входящем звонке (WS событие и VoIP-пуш)
	public static function sendIncomingCall(int $user_id, int $caller_user_id, array $formatted_call, array $push_data, array $node_list, array $ws_users):void {

		// получаем параметры запроса
		$ar_post = self::_prepareSendIncomingCallParameters($user_id, $ws_users, $caller_user_id, $formatted_call, $node_list, $push_data);

		// подготавливаем event_data (шифруем map -> key)
		$ar_post = Type_Pack_Main::replaceMapWithKeys($ar_post);

		// проводим тест безопасности, что в ответе нет map
		Type_Pack_Main::doSecurityTest($ar_post);

		$converted_event_version_list = self::_convertEventVersionListToGrpcStructure($ar_post["event_version_list"]);
		$request                      = new \SenderGrpc\SenderSendIncomingCallRequestStruct([
			"company_id"         => COMPANY_ID,
			"user_id"            => $ar_post["user_id"],
			"event_version_list" => $converted_event_version_list,
			"push_data"          => toJson($ar_post["push_data"]),
			"ws_users"           => toJson($ar_post["ws_users"]),
			"uuid"               => $ar_post["uuid"],
			"time_to_live"       => $ar_post["time_to_live"],
		]);

		/** @noinspection PhpParamsInspection $request что ты такое */
		[, $status] = self::_doCallGrpc("SenderSendIncomingCall", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// функция отправляет только ws событие о входящем звонке
	public static function sendIncomingCallEvent(int $user_id, int $caller_user_id, array $formatted_call, array $node_list, array $ws_users, array $push_data = []):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallIncoming_V1::makeEvent($formatted_call, $node_list, $caller_user_id),
		], [$talking_user_schema], $push_data, $ws_users);
	}

	// событие успешной инициализации звонка
	public static function callInited(int $user_id, array $call):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallInited_V1::makeEvent($call),
		], [$talking_user_schema]);
	}

	// событие о завершенном звонке
	public static function callFinished(int $user_id, array $call):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallFinished_V1::makeEvent($call),
		], [$talking_user_schema]);
	}

	// событие о сбросе трубки
	public static function callHangup(int $user_id, int $causer_user_id, string $call_map, array $call):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallHangup_V1::makeEvent($call, $call_map, $causer_user_id),
		], [$talking_user_schema], ws_users: [$causer_user_id]);
	}

	// событие о потере соединения
	public static function callConnectionLost(array $talking_user_list, string $call_map, int $causer_user_id, string $connection_uuid, int $is_publisher):void {

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallConnectionLost_V1::makeEvent($call_map, $causer_user_id, $connection_uuid, $is_publisher),
		], $talking_user_list);
	}

	// событие о переключении аудио/видео
	public static function callMediaChanged(array $talking_user_list, string $call_map, int $causer_user_id, bool $audio, bool $video):void {

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallMediaChanged_V1::makeEvent($call_map, $causer_user_id, $audio ? 1 : 0, $video ? 1 : 0),
		], $talking_user_list);
	}

	// событие начала звонка
	public static function callSpeakStarted(int $user_id, array $call, string $call_map):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallSpeakStarted_V1::makeEvent($call, $call_map),
		], [$talking_user_schema]);
	}

	// событие о принятии звонка
	public static function callAccepted(array $talking_user_list, int $user_id, string $call_map):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallAccepted_V1::makeEvent($call_map, $user_id),
		], $talking_user_list, ws_users: [$user_id]);
	}

	// событие о приглашении нового участника к звонку
	public static function callMemberInvited(int $user_id, int $invited_by_user_id, int $opponent_user_id, string $call_map, array $call):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallMemberInvited_V1::makeEvent($call, $invited_by_user_id, $opponent_user_id, $call_map),
		], [$talking_user_schema], ws_users: [$opponent_user_id]);
	}

	// событие об исключении участника в звонке
	public static function callMemberKicked(int $user_id, int $kicked_by_user_id, int $opponent_user_id, string $call_map, array $call):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallMemberKicked_V1::makeEvent($call, $kicked_by_user_id, $opponent_user_id, $call_map),
		], [$talking_user_schema], ws_users: [$opponent_user_id]);
	}

	// событие о присоединии нового участника к звонку
	public static function callMemberPublisherEstablished(int $user_id, int $opponent_user_id, string $call_map, array $sub_connection_data):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallMemberPublisherEstablished_V1::makeEvent($sub_connection_data, $opponent_user_id, $call_map),
		], [$talking_user_schema], ws_users: [$opponent_user_id]);
	}

	// событие о подключении участника к звонку
	public static function callMemberConnectionEstablished(int $user_id, int $causer_user_id, string $call_map, array $call):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_CallMemberPublisherEstablished_V1::makeEvent($call, $causer_user_id, $call_map),
		], [$talking_user_schema], ws_users: [$causer_user_id]);
	}

	// -------------------------------------------------------
	// OTHER METHODS
	// -------------------------------------------------------

	// отправить VoIP push
	public static function sendVoIPPush(int $user_id, array $push_data, bool $is_voip_live = false):void {

		$ar_post = [
			"method"    => "talking.sendVoIP",
			"user_id"   => $user_id,
			"push_data" => $push_data,
			"uuid"      => generateUUID(),
		];

		// если нужно указать время жизни для voip пуша
		if ($is_voip_live) {
			$ar_post["time_to_live"] = Gateway_Db_CompanyCall_CallMonitoringDialing::DIALING_TIMEOUT;
		}

		// подготавливаем event_data (шифруем map -> key)
		$ar_post = Type_Pack_Main::replaceMapWithKeys($ar_post);

		// проводим тест безопасности, что в ответе нет map
		Type_Pack_Main::doSecurityTest($ar_post);

		$request = new \SenderGrpc\SenderSendVoIPRequestStruct([
			"user_id"    => $ar_post["user_id"],
			"push_data"  => toJson($ar_post["push_data"]),
			"uuid"       => generateUUID(),
			"company_id" => COMPANY_ID,
		]);

		/** @noinspection PhpParamsInspection $request что ты такое */
		[, $status] = self::_doCallGrpc("SenderSendVoIP", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// формируем talking_user_item
	#[ArrayShape(["user_id" => "int", "need_push" => "int"])]
	public static function makeTalkingUserItem(int $user_id, bool $is_need_push):array {

		return [
			"user_id"   => $user_id,
			"need_push" => $is_need_push ? 1 : 0,
		];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * отправляем событие
	 *
	 * @param Struct_Sender_Event[] $event_version_list
	 * @param array                 $user_list
	 * @param array                 $push_data
	 * @param array                 $ws_users
	 *
	 * @throws ParseFatalException
	 */
	protected static function _sendEvent(array $event_version_list, array $user_list, array $push_data = [], array $ws_users = []):void {

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

		self::_sendEventRequest($event_name, $user_list, $converted_event_version_list, $ws_users, $push_data);
	}

	/**
	 * проверяем параметры
	 *
	 * @param Struct_Sender_Event[] $event_version_list
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
	 * Отправить событие в go_sender
	 *
	 * @long
	 */
	protected static function _sendEventRequest(string $event, array $user_list, array $event_version_list, array $ws_user_list = [], array $push_data = [], string $routine_key = ""):void {

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "sender.sendEvent",
			"user_list"          => (array) $user_list,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_user_list,
			"routine_key"        => (string) $routine_key,
		];
		$params = self::_prepareParams($params);

		// подготавливаем event_data (шифруем map -> key)
		$params = Type_Pack_Main::replaceMapWithKeys($params);

		// проводим тест безопасности, что в ответе нет map
		Type_Pack_Main::doSecurityTest($params);

		$user_list                    = self::_convertReceiverUserListToGrpcStructure($params["user_list"]);
		$converted_event_version_list = self::_convertEventVersionListToGrpcStructure($params["event_version_list"]);
		$grpc_request                 = new \SenderGrpc\SenderSendEventRequestStruct([
			"user_list"          => $user_list,
			"event"              => $params["event"],
			"event_version_list" => $converted_event_version_list,
			"push_data"          => toJson($params["push_data"]),
			"uuid"               => $params["uuid"],
			"ws_users"           => isset($params["ws_users"]) ? toJson($params["ws_users"]) : "",
			"company_id"         => COMPANY_ID,
		]);

		try {

			/** @noinspection PhpParamsInspection $grpc_request что ты такое */
			[, $status] = self::_doCallGrpc("SenderSendEvent", $grpc_request);
			if ($status->code !== \Grpc\STATUS_OK) {
				throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
			}
		} catch (\Error | \busException) {

			Type_System_Admin::log("go_sender", "go_sender call grpc on SenderSendEvent");

			// отправляем задачу в rabbitMq
			\Bus::rabbitSendToExchange("go_sender", $params);
		}
	}

	// подготавливаем $params
	protected static function _prepareParams(array $params):array {

		// если ws_user_list не пуст
		if (count($params["ws_users"]) > 0) {

			// добавляем к параметрам задачи
			$params["ws_users"] = (object) self::makeUsers($params["ws_users"]);
		}

		return $params;
	}

	/**
	 * конвертируем user_list в структуру понятную grpc
	 *
	 * @param array $user_list
	 *
	 * @return array
	 */
	protected static function _convertReceiverUserListToGrpcStructure(array $user_list):array {

		$output = [];
		foreach ($user_list as $user_item) {

			$output[] = new \SenderGrpc\EventUserStruct([
				"user_id"   => $user_item["user_id"],
				"need_push" => $user_item["need_push"],
			]);
		}

		return $output;
	}

	/**
	 * конвертируем event_version_list в структуру понятную grpc
	 *
	 * @param array $event_version_list
	 *
	 * @return array
	 */
	protected static function _convertEventVersionListToGrpcStructure(array $event_version_list):array {

		$output = [];
		foreach ($event_version_list as $event_version_item) {

			$output[] = new \SenderGrpc\EventVersionItem([
				"version" => (int) $event_version_item["version"],
				"data"    => toJson((object) $event_version_item["data"]),
			]);
		}

		return $output;
	}

	// собираем параметры запроса sendIncomingCall
	// @long собираем большой $ar_post
	#[ArrayShape(["method" => "string", "user_id" => "int", "event_version_list" => "array", "push_data" => "object", "uuid" => "string", "time_to_live" => "int", "ws_users" => "object"])]
	protected static function _prepareSendIncomingCallParameters(int $user_id, array $ws_users, int $caller_user_id, array $formatted_call, array $node_list, array $push_data):array {

		$ws_users = self::makeUsers($ws_users);

		// формируем версии события
		$event_version_list = [
			Gateway_Bus_Sender_Event_CallIncoming_V1::makeEvent($formatted_call, $node_list, $caller_user_id),
		];

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list = [];
		foreach ($event_version_list as $event) {

			$converted_event_version_list[] = [
				"version" => (int) $event->version,
				"data"    => (object) $event->ws_data,
			];
		}

		return [
			"method"             => (string) "talking.sendIncomingCall",
			"user_id"            => (int) $user_id,
			"event_version_list" => (array) $converted_event_version_list,
			"push_data"          => (object) $push_data,
			"uuid"               => (string) generateUUID(),
			"time_to_live"       => (int) Gateway_Db_CompanyCall_CallMonitoringDialing::DIALING_TIMEOUT,
			"ws_users"           => (object) $ws_users,
		];
	}

	// -------------------------------------------------------
	// WS_USERS
	// -------------------------------------------------------

	/**
	 * формируем объект ws_users
	 *
	 * @param array $user_list
	 *
	 * @return array[]
	 */
	#[ArrayShape(["user_list" => "array", "signature" => "string"])]
	public static function makeUsers(array $user_list):array {

		// если вдруг есть смещение ключей в массиве, убираем
		$user_list = array_values($user_list);

		// принудительно приводим id пользователя к int
		$user_list = arrayValuesInt($user_list);

		return [
			"user_list" => (array) $user_list,
			"signature" => (string) Type_Api_Action::getUsersSignature($user_list, time()),
		];
	}

	// делаем grpc запрос к указанному методу с переданными данными
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("sender", \SenderGrpc\senderClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}