<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для таблицы company_call.janus_room
 */
class Gateway_Db_CompanyCall_JanusRoom extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_KEY = "janus_room";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// создаем запись
	public static function insert(array $insert):int {

		return ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert, false);
	}

	// обновляем запись
	public static function set(int $room_id, array $set):void {

		ShardingGateway::database(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE `room_id` = ?i LIMIT ?i",
			self::_TABLE_KEY, $set, $room_id, 1);
	}

	// получаем запись из таблицы
	public static function getOne(int $room_id):array {

		$query = "SELECT * FROM `?p` WHERE `room_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $room_id, 1);
	}

	// получаем запись из таблицы
	public static function getOneByCallMap(string $call_map):array {

		// запрос проверен на EXPLAIN (INDEX='call_map')
		$query = "SELECT * FROM `?p` WHERE `call_map` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $call_map, 1);
	}
}
