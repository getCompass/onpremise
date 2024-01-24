<?php

namespace Compass\Speaker;

/**
 * класс для отправки нотисов
 */
class Gateway_Notice_Sender {

	/**
	 * отправить сообщение пользователю
	 *
	 */
	public static function sendUser(int $user_id, string $text):void {

		self::_call("messages.addSingle", [
			"user_id" => $user_id,
			"text"    => SERVER_NAME . "\n{$text}",
		]);
	}

	/**
	 * отправить сообщение в группу
	 *
	 */
	public static function sendGroup(string $conversation_key, string $text):void {

		self::_call("messages.addGroup", [
			"conversation_key" => $conversation_key,
			"text"             => $text,
		]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

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
		$response = fromJson($response);

		if (!isset($response["status"]) || $response["status"] != "ok") {
			Type_System_Admin::log("notice", formatArgs($payload) . "\n" . formatArgs($response));
		}
	}
}
