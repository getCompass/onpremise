<?php

namespace Compass\Jitsi;

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
	 * формируем объект с пуш уведомлением для Jitsi
	 *
	 * @param array $data
	 * @param array $conference_data
	 * @param array $conference_joining_data
	 * @param array $conference_member_data
	 * @param array $conference_creator_data
	 * @param bool  $is_need_send_apns
	 *
	 * @return array
	 */
	#[ArrayShape(["push_type" => "int", "voip_push" => "array"])]
	public static function makeVoIPPushData(array $data, array $conference_data,
							    array $conference_joining_data,
							    array $conference_member_data,
							    array $conference_creator_data,
							    bool $is_need_send_apns = true):array {

		$voip_push = [
			"action"                  => $data["action"],
			"conference_data"         => $conference_data,
			"conference_joining_data" => $conference_joining_data,
			"conference_member_data"  => $conference_member_data,
			"conference_creator_data" => $conference_creator_data,
			"is_need_send_apns"       => $is_need_send_apns ? 1 : 0,
		];
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

	// содержание для voip пуша
	public static function getVoIPPushBody(string $status, int $user_id = 0):array {

		switch ($status) {

			case "new":

				$push_body["action"]       = "incoming";
				$push_body["time_to_live"] = Domain_PhpJitsi_Entity_Event_NeedCheckSingleConference::NEED_WORK_INTERVAL;
				$push_body["user_id"]      = $user_id;
				break;

			case "finished":

				$push_body["action"] = "finished";
				break;
			default:

				throw new \paramException("unknown status of call");
		}

		return $push_body;
	}
}
