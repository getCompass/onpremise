<?php

namespace Compass\Jitsi;

use GetCompass\Userbot\Bot;

/**
 * класс для отправки уведомлений в Compass
 */
class Type_Notice_Compass {

	// timeout в секундах
	protected const _TIMEOUT = 10;

	/**
	 * отправляем сообщение в группу
	 */
	public static function sendGroup(string $project, string $token, string $group_id, string $text):void {

		$ar_post  = [
			"project"  => $project,
			"token"    => $token,
			"method"   => "group.send",
			"group_id" => $group_id,
			"message"  => $text,
		];
		$response = self::_sendRequest(COMPASS_NOTICE_ENDPOINT, $ar_post);

		if (!isset($response["status"]) || $response["status"] != "ok") {

			Type_System_Admin::log("compass_notice", [
				"response" => $response,
				"result"   => "unsuccessful request",
			]);
		}
	}

	/**
	 *
	 * Отправляем сообщение в группу
	 */
	public static function sendGroupNew(string $token, string $signature_key, string $group_id, string $text):void {

		$bot        = new Bot($token, $signature_key);
		$message_id = $bot->sendGroupMessage($group_id, $text);
		if (mb_strlen($message_id) < 1) {

			Type_System_Admin::log("compass_notice", [
				"result" => "unsuccessful request",
			]);
		}
	}

	/**
	 * отправляем запрос
	 */
	protected static function _sendRequest(string $endpoint, array $ar_post):array {

		$curl = new \Curl();
		$curl->setTimeout(self::_TIMEOUT);

		$response = $curl->post($endpoint, $ar_post);

		return json_decode($response, true, 512, JSON_BIGINT_AS_STRING);
	}
}