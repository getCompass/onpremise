<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Class Gateway_Bus_Pusher
 */
class Gateway_Bus_Pusher {

	/**
	 * отправляем пустое push уведомление для проверки работоспособности отправки пуш уведомлений
	 *
	 * @param int    $user_id
	 * @param array  $token_item
	 * @param string $push_type
	 *
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function sendTestPush(int $user_id, array $token_item, string $push_type = "TEST_MESSAGE"):void {

		// форматируем tokenItem под микросервис
		$token_item = self::_prepareTestToken($token_item);

		// собираем массив параметров
		$request = new \PusherGrpc\PusherSendTestPushRequestStruct([
			"user_id"    => $user_id,
			"token_item" => new \PusherGrpc\TokenItem([
				"version"              => $token_item["version"],
				"token"                => $token_item["token"],
				"platform"             => $token_item["platform"],
				"token_type"           => $token_item["token_type"],
				"session_uniq"         => $token_item["session_uniq"],
				"device_id"            => $token_item["device_id"],
				"sound_type"           => $token_item["sound_type"],
				"is_new_firebase_push" => $token_item["is_new_firebase_push"],
				"app_name"             => $token_item["app_name"],
			]),
			"type"       => $push_type,
		]);

		[$_, $status] = self::_doCallGrpc("PusherSendTestPush", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code, true);
		}
	}

	/**
	 * Обновить количество непрочитанных сообщений на устройствах пользователя
	 *
	 */
	public static function updateBadgeCount(array $device, int $badge_count, array $conversation_key_list = [], array $thread_key_list = []):void {

		$device = new Struct_Db_PivotData_Device($device["device_id"], $device["user_id"], $device["created_at"], $device["updated_at"], $device["extra"]);

		// формируем массив для запроса
		$ar_post = [
			"method"                => "pusher.updateBadge",
			"device"                => $device,
			"badge_count"           => $badge_count,
			"conversation_key_list" => $conversation_key_list,
			"thread_key_list"       => $thread_key_list,
		];

		// отправляем задачу в очередь
		ShardingGateway::rabbit()->sendMessageToExchange("go_pusher_exchange", $ar_post);
	}

	// добавляем недостащие поля для токенов старых версий, начиная с версии 2 (1 больше нет на паблике)
	protected static function _prepareTestToken(array $test_token_item):array {

		// если не задан device_id
		if (!isset($test_token_item["device_id"])) {
			$test_token_item["device_id"] = generateUUID();
		}

		// если не задан sound_type
		if (!isset($test_token_item["sound_type"])) {
			$test_token_item["sound_type"] = Type_User_Notifications::SOUND_TYPE_1;
		}

		// если не задан is_new_firebase_push
		if (!isset($test_token_item["is_new_firebase_push"])) {
			$test_token_item["is_new_firebase_push"] = 0;
		}

		// если не задана platform
		if (!isset($test_token_item["platform"])) {
			$test_token_item["platform"] = "";
		}

		// если не задана app_name
		if (!isset($test_token_item["app_name"])) {
			$test_token_item["app_name"] = "comteam";
		}

		return $test_token_item;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("pusher", \PusherGrpc\pusherClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}