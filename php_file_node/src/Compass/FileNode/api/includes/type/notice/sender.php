<?php

namespace Compass\FileNode;

/**
 * Класс для отправки нотисов через noticebot
 */
class Type_Notice_Sender {

	// отправить сообщение пользователю
	public static function sendUser(int $user_id, string $text):void {

		self::_call("messages.addSingle", [
			"user_id" => $user_id,
			"text"    => SERVER_NAME . "\n{$text}",
		]);
	}

	// отправить сообщение в группу
	public static function sendGroup(string $channel, string $text):void {

		// получаем список каналов
		$channel_list = getConfig("NOTICE_CHANNEL_LIST");

		// если такого не существует то выбрасываем exception
		if (!isset($channel_list[$channel])) {
			throw new \ParseException(__METHOD__ . ": trying to send message in group, which not exists in configuration");
		}

		self::_call("messages.addGroup", [
			"conversation_key" => $channel_list[$channel],
			"text"             => SERVER_NAME . "\n{$text}",
		]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// дергаем бота
	protected static function _call(string $method, array $payload):void {

		$payload["method"]      = $method;
		$payload["bot_user_id"] = NOTICE_BOT_USER_ID;

		$json    = toJson($payload);
		$ar_post = [
			"payload"   => $json,
			"signature" => hash_hmac("sha256", $json, NOTICE_BOT_TOKEN),
		];

		$curl     = new \Curl();
		$response = $curl->post(NOTICE_ENDPOINT, $ar_post);

		if (!isset($response["status"]) || $response["status"] != "ok") {
			Type_System_Admin::log("chat_notice", dd($payload) . "\n" . dd($response));
		}
	}
}
