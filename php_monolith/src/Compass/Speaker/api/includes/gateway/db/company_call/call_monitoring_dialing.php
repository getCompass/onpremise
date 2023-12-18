<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для таблицы company_call.call_monitoring_dialing
 */
class Gateway_Db_CompanyCall_CallMonitoringDialing extends Gateway_Db_CompanyCall_Main {

	public const _TABLE_KEY = "call_monitoring_dialing";

	public const DIALING_TIMEOUT = 45; // timeout dialing этапа в секундах

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// создаем задачу
	public static function addTask(int $user_id, string $call_map):void {

		$insert = [
			"call_map"    => $call_map,
			"user_id"     => $user_id,
			"need_work"   => time() + self::DIALING_TIMEOUT,
			"error_count" => 0,
			"created_at"  => time(),
		];

		ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_TABLE_KEY, $insert);
	}

	// удаляем записи по call_map
	public static function deleteByCallMap(string $call_map):void {

		$count_query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `call_map` = ?s LIMIT ?i";
		$count       = ShardingGateway::database(self::_getDbKey())->getOne($count_query, self::_TABLE_KEY, $call_map, 1)["count"];

		ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `call_map` = ?s LIMIT ?i",
			self::_TABLE_KEY, $call_map, $count);
	}

	// удаляем запись
	public static function delete(int $user_id, string $call_map):void {

		ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `user_id` = ?i AND `call_map` = ?s LIMIT ?i",
			self::_TABLE_KEY, $user_id, $call_map, 1);
	}

	// удаляем несколько записей
	public static function deleteForUsers(array $user_id_list, string $call_map):void {

		ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `user_id` IN (?a) AND `call_map` = ?s LIMIT ?i",
			self::_TABLE_KEY, $user_id_list, $call_map, count($user_id_list));
	}

	// получаем запись
	public static function getAll(int $limit, int $offset):array {

		return ShardingGateway::database(self::_getDbKey())->getAll("SELECT * FROM `?p` WHERE ?i = ?i LIMIT ?i OFFSET ?i",
			self::_TABLE_KEY, 1, 1, $limit, $offset);
	}

	// обновляем запись
	public static function set(int $user_id, string $call_map, array $set):void {

		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `call_map` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $user_id, $call_map, 1);
	}
}