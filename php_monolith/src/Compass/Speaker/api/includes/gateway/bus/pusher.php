<?php

namespace Compass\Speaker;

use JetBrains\PhpStorm\ArrayShape;

/**
 * класс для работы с go_pusher
 */
class Gateway_Bus_Pusher {

	protected const _VOIP_PUSH_TYPE = 2;

	// -------------------------------------------------------
	// методы для работы с объектом push уведомления
	// -------------------------------------------------------

	/**
	 * формируем объект с пуш уведомлением для сообщения в диалоге
	 *
	 * @param array $call
	 * @param array $data
	 * @param bool  $is_need_send_apns
	 *
	 * @return array
	 */
	#[ArrayShape(["push_type" => "int", "voip_push" => "array"])]
	public static function makeVoIPPushData(array $call, array $data, bool $is_need_send_apns = true):array {

		$voip_push = [
			"company_id"        => COMPANY_ID,
			"call"              => $call,
			"action"            => $data["action"],
			"is_need_send_apns" => $is_need_send_apns ? 1 : 0,
		];
		if (isset($data["node_list"])) {
			$voip_push["node_list"] = $data["node_list"];
		}
		if (isset($data["time_to_live"])) {
			$voip_push["time_to_live"] = $data["time_to_live"];
		}
		if (isset($data["user_id"])) {
			$voip_push["user_id"] = $data["user_id"];
		}

		return [
			"push_type" => self::_VOIP_PUSH_TYPE,
			"voip_push" => $voip_push,
		];
	}
}
