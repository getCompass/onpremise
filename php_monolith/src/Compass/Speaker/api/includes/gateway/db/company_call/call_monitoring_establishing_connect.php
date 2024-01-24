<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для таблицы company_call.call_monitoring_establishing_connect
 */
class Gateway_Db_CompanyCall_CallMonitoringEstablishingConnect extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_KEY = "call_monitoring_establishing_connect";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// создаем запись
	public static function insert(array $insert):int {

		return ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_TABLE_KEY, $insert);
	}

	// удаляем запись
	public static function delete(string $call_map, int $user_id):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `call_map` = ?s AND `user_id` = ?i LIMIT ?i",
			self::_TABLE_KEY, $call_map, $user_id, 1);
	}

	// удаляем запись по call_map
	public static function deleteByCallMap(string $call_map):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `call_map` = ?s LIMIT ?i", self::_TABLE_KEY, $call_map, 1);
	}

	// удаляем несколько записей
	public static function deleteForUsers(array $user_id_list, string $call_map):void {

		ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `user_id` IN (?a) AND `call_map` = ?s LIMIT ?i",
			self::_TABLE_KEY, $user_id_list, $call_map, count($user_id_list));
	}

	// получаем запись
	public static function get(string $call_map, int $user_id):array {

		return ShardingGateway::database(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE `call_map` = ?s AND `user_id` = ?i LIMIT ?i",
			self::_TABLE_KEY, $call_map, $user_id, 1);
	}

	// получаем запись
	public static function getAll(int $limit, int $offset):array {

		return ShardingGateway::database(self::_getDbKey())->getAll("SELECT * FROM `?p` WHERE ?i = ?i LIMIT ?i OFFSET ?i",
			self::_TABLE_KEY, 1, 1, $limit, $offset);
	}

	// обновляем запись
	public static function set(string $call_map, int $user_id, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `call_map` = ?s AND `user_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $call_map, $user_id, 1);
	}
}