<?php

namespace Application\System;

use Compass\Conversation\Type_System_Admin;

/**
 * класс для отправки нотисов
 */
class Notice {

	public function __construct(
		protected string $_endpoint,
		protected int    $_bot_user_id,
		protected string $_bot_token,
	) {
	}

	/**
	 * отправить сообщение пользователю
	 */
	public function sendUser(int $user_id, string $text):void {

		self::_call("messages.addSingle", [
			"user_id" => $user_id,
			"text"    => SERVER_NAME . "\n{$text}",
		]);
	}

	/**
	 * отправить сообщение в группу
	 */
	public function sendGroup(string $conversation_key, string $text):void {

		self::_call("messages.addGroup", [
			"conversation_key" => $conversation_key,
			"text"             => $text,
		]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	protected function _call(string $method, array $payload):void {

		$payload["method"]      = $method;
		$payload["bot_user_id"] = $this->_bot_user_id;

		$json    = toJson($payload);
		$ar_post = [
			"payload"   => $json,
			"signature" => hash_hmac("sha256", $json, $this->_bot_token),
		];

		$curl     = new \Curl();
		$response = $curl->post($this->_endpoint, $ar_post);
		$response = fromJson($response);

		if (!isset($response["status"]) || $response["status"] != "ok") {
			Type_System_Admin::log("notice", formatArgs($payload) . "\n" . formatArgs($response));
		}
	}
}
