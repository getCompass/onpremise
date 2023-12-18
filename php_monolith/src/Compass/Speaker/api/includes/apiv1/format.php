<?php

namespace Compass\Speaker;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Класс для форматирование сущностей под формат API
 *
 * В коде мы оперируем своими структурами и понятиями
 * К этому классу обращаемся строго отдачей результата в API
 * Для форматирования стандартных сущностей
 *
 */
class Apiv1_Format {

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// звонок
	#[ArrayShape(["call_map" => "string", "status" => "string", "type" => "string", "creator_user_id" => "int", "users" => "array", "data" => "object", "member_list" => "array", "finished_at" => "int", "started_at" => "int"])]
	public static function call(array $call_info):array {

		$output = [
			"call_map"        => (string) $call_info["call_map"],
			"status"          => (string) $call_info["status"],
			"type"            => (string) $call_info["type"],
			"creator_user_id" => (int) $call_info["creator_user_id"],
			"users"           => (array) $call_info["users"],
			"data"            => (object) self::_callData($call_info["data"]),
			"member_list"     => (array) $call_info["member_list"],
			"current_at_ms"   => (int) (microtime(true) * 1000),
		];

		// добавляем к ответу, если время начала разговора больше нуля
		if ($call_info["started_at"] > 0) {
			$output["started_at"] = (int) $call_info["started_at"];
		}

		// добавляем к ответу, если время завершения разговора больше нуля
		if ($call_info["finished_at"] > 0) {
			$output["finished_at"] = (int) $call_info["finished_at"];
		}

		return $output;
	}

	// поле data в call
	protected static function _callData(array $data):array {

		$output["report_call_id"] = (int) $data["report_call_id"];

		if (isset($data["conversation_map"])) {
			$output["conversation_map"] = (string) $data["conversation_map"];
		}

		if (isset($data["is_lost_connection"])) {
			$output["is_lost_connection"] = (int) $data["is_lost_connection"];
		}

		if (isset($data["finished_reason"])) {
			$output["finished_reason"] = (string) $data["finished_reason"];
		}

		if (isset($data["hangup_by_user_id"])) {
			$output["hangup_by_user_id"] = (int) $data["hangup_by_user_id"];
		}

		if (isset($data["opponent_user_id"])) {
			$output["opponent_user_id"] = (int) $data["opponent_user_id"];
		}

		return $output;
	}

	// объект janus_communication, содержащий все необходимые клиенту параметры для установления прямой коммуникации с janus сервером
	public static function janusCommunicationSingle(array $janus_communication_info):array {

		return [
			"session_id"          => (int) $janus_communication_info["session_id"],
			"pub_handle_id"       => (int) $janus_communication_info["pub_handle_id"],
			"sub_handle_id"       => (int) $janus_communication_info["sub_handle_id"],
			"token"               => (string) $janus_communication_info["token"],
			"url"                 => (string) $janus_communication_info["url"],
			"event_endpoint"      => (string) $janus_communication_info["event_endpoint"],
			"pub_handle_endpoint" => (string) $janus_communication_info["pub_handle_endpoint"],
			"sub_handle_endpoint" => (string) $janus_communication_info["sub_handle_endpoint"],
			"publisher_user_id"   => (int) $janus_communication_info["publisher_user_id"],
			"room_id"             => (int) $janus_communication_info["room_id"],
			"room_token"          => (string) $janus_communication_info["room_token"],
			"participant_id"      => (int) $janus_communication_info["participant_id"],
		];
	}

	// объект ice_server_data, содержащий все неободимые клиенту параметры для создания peer_connection соединения
	#[ArrayShape(["turn_list" => "array", "stun_list" => "array", "ice_transport_policy" => "string"])]
	public static function iceServerData(array $ice_server_data):array {

		return [
			"turn_list"            => (array) $ice_server_data["turn_list"],
			"stun_list"            => (array) $ice_server_data["stun_list"],
			"ice_transport_policy" => (string) $ice_server_data["ice_transport_policy"],
		];
	}

	// объект sub_connection_data, содержащий всю необходимую клиенту информацию как устанавливать peer_connection subscriber соединения
	public static function subConnectionData(array $sub_connection_data):array {

		return [
			"handle_id"         => (int) $sub_connection_data["handle_id"],
			"handle_endpoint"   => (string) $sub_connection_data["handle_endpoint"],
			"publisher_user_id" => (int) $sub_connection_data["publisher_user_id"],
			"room_id"           => (int) $sub_connection_data["room_id"],
			"participant_id"    => (int) $sub_connection_data["participant_id"],
			"connection_uuid"   => (string) $sub_connection_data["connection_uuid"],
			"is_enabled_audio"  => (int) $sub_connection_data["is_enabled_audio"],
			"is_enabled_video"  => (int) $sub_connection_data["is_enabled_video"],
			"ice_server_data"   => (object) self::iceServerData($sub_connection_data["ice_server_data"]),
			"token"             => (string) $sub_connection_data["token"],
		];
	}

	// объект janus_communication_data, содержащий всю необходимую клиенту информацию как установить все peer_connection соединения звонка
	#[ArrayShape(["session_id" => "int", "event_endpoint" => "string", "token" => "string", "pub_connection_data" => "object", "sub_connection_data" => "array"])]
	public static function janusCommunicationData(array $janus_communication_data):array {

		$sub_connection_data = [];
		foreach ($janus_communication_data["sub_connection_data"] as $item) {
			$sub_connection_data[] = self::subConnectionData($item);
		}

		return [
			"session_id"          => (int) $janus_communication_data["session_id"],
			"event_endpoint"      => (string) $janus_communication_data["event_endpoint"],
			"token"               => (string) $janus_communication_data["token"],
			"pub_connection_data" => (object) $janus_communication_data["pub_connection_data"],
			"sub_connection_data" => (array) $sub_connection_data,
		];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------
}

