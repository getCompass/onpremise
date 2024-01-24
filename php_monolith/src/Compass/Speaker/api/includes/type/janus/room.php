<?php

namespace Compass\Speaker;

/**
 * класс предназначен для работы с сущностью разговорной комнаты в Janus WebRTC Server
 */
class Type_Janus_Room {

	// создать запись с разговорной комнатой звонка
	public static function insert(string $call_map, int $node_id, int $room_id, int $session_id, int $handle_id):array {

		// пытаемся занести запись
		$insert_room_row = self::_prepareRowArray($call_map, $node_id, $room_id, $session_id, $handle_id);
		try {
			Gateway_Db_CompanyCall_JanusRoom::insert($insert_room_row);
		} catch (\PDOException $e) {

			// если это дупликат
			if ($e->getCode() == 23000) {
				throw new cs_Janus_CallRoomAlreadyExist();
			}

			throw $e;
		}

		return $insert_room_row;
	}

	// подготавливаем массив для записи с разговорной комнатой
	protected static function _prepareRowArray(string $call_map, int $node_id, int $room_id, int $session_id, int $handle_id):array {

		return [
			"room_id"    => $room_id,
			"node_id"    => $node_id,
			"bitrate"    => Type_Janus_Api::MAX_ROOM_BITRATE,
			"call_map"   => $call_map,
			"session_id" => $session_id,
			"handle_id"  => $handle_id,
			"created_at" => time(),
			"updated_at" => 0,
		];
	}

	// получить запись с разговорной комнатой звонка по его call_map
	public static function getByCallMap(string $call_map):array {

		return Gateway_Db_CompanyCall_JanusRoom::getOneByCallMap($call_map);
	}

	// обновляем разговорную комнату
	public static function updateRoom(int $room_id, array $set):void {

		Gateway_Db_CompanyCall_JanusRoom::set($room_id, $set);
	}
}
