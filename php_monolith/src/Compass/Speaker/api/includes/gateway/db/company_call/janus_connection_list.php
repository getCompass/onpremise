<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для таблицы company_call.janus_connection_list
 */
class Gateway_Db_CompanyCall_JanusConnectionList extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_KEY = "janus_connection_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// создаем запись
	public static function insert(array $insert):int {

		return ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	// обновляем запись
	public static function set(int $session_id, int $handle_id, array $set):void {

		ShardingGateway::database(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE `session_id` = ?i AND `handle_id` = ?i LIMIT ?i",
			self::_TABLE_KEY, $set, $session_id, $handle_id, 1);
	}

	// получаем запись из таблицы
	public static function get(int $session_id, int $handle_id):array {

		return ShardingGateway::database(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE `session_id` = ?i AND `handle_id` = ?i LIMIT ?i",
			self::_TABLE_KEY, $session_id, $handle_id, 1);
	}

	// получаем все записи по ключу call_map
	public static function getAllByCallMap(string $call_map, int $limit):array {

		// запрос проверен на EXPLAIN (INDEX=call_map)
		return ShardingGateway::database(self::_getDbKey())->getAll("SELECT * FROM `?p` WHERE `call_map` = ?s LIMIT ?i",
			self::_TABLE_KEY, $call_map, $limit);
	}

	// получаем запись из таблицы по ключу connection_uuid
	public static function getOneByConnectionUUID(string $connection_uuid):array {

		$query = "SELECT * FROM `?p` WHERE `connection_uuid` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $connection_uuid, 1);
	}

	// получаем запись паблишера из таблицы по ключам call_map & user_id
	public static function getPublisherByCallMap(string $call_map, int $user_id):array {

		// запрос проверен на EXPLAIN (INDEX=user_id_call_map)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `call_map` = ?s AND `is_publisher` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $user_id, $call_map, 1, 1);
	}

	// получаем запись из таблицы по ключам call_map и user_id
	public static function getAllUserConnectionsByCallMap(string $call_map, int $user_id):array {

		// запрос проверен на EXPLAIN (INDEX='user_id_call_map')
		$count_query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `user_id` = ?i AND `call_map` = ?s LIMIT ?i";
		$count       = ShardingGateway::database(self::_getDbKey())->getOne($count_query, self::_TABLE_KEY, $user_id, $call_map, 1)["count"];

		// запрос проверен на EXPLAIN (INDEX='user_id_call_map')
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `call_map` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, $user_id, $call_map, $count);
	}

	// удалить запись из таблицы
	public static function delete(int $session_id, int $handle_id):void {

		$query = "DELETE FROM `?p` WHERE `session_id` = ?i AND `handle_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->delete($query, self::_TABLE_KEY, $session_id, $handle_id, 1);
	}

	// получить все publisher-соединения разговора
	public static function getPublisherListByCallMap(string $call_map):array {

		// запрос проверен на EXPLAIN (INDEX='call_map_is_publisher')
		$query = "SELECT * FROM `?p` WHERE `call_map` = ?s AND `is_publisher` = ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, $call_map, 1, CALL_MAX_MEMBER_LIMIT);

		$output = [];
		foreach ($list as $item) {
			$output[$item["user_id"]] = $item;
		}

		return $output;
	}

	// получить все subscriber-соединения которые слушают конкретного publisher_user_id
	public static function getSubscriberListByPublisherUserId(string $call_map, int $publisher_user_id, int $number_of_members):array {

		// запрос проверен на EXPLAIN (INDEX='call_map_publisher_user_id')
		$query = "SELECT * FROM `?p` USE INDEX(`?p`) WHERE `call_map` = ?s AND `publisher_user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, "call_map_publisher_user_id", $call_map, $publisher_user_id, $number_of_members);
	}
}
