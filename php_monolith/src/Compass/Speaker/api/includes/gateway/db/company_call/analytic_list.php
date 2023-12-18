<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для работы с таблицей company_call.analytic_list
 */
class Gateway_Db_CompanyCall_AnalyticList extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_KEY = "analytic_list";

	// получить запись
	public static function getOne(string $call_map, int $user_id):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `call_map` = ?s AND `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $call_map, $user_id, 1);
	}

	// добавляем запись
	public static function insert(string $call_map, int $user_id, int $report_call_id, int $task_id):void {

		$insert = [
			"call_map"             => $call_map,
			"user_id"              => $user_id,
			"report_call_id"       => $report_call_id,
			"reconnect_count"      => 0,
			"middle_quality_count" => 0,
			"created_at"           => time(),
			"updated_at"           => time(),
			"task_id"              => $task_id,
			"last_row_id"          => 0,
		];
		ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	// обновляем запись
	public static function set(int $task_id, array $set):void {

		// запрос проверен на EXPLAIN (INDEX='task_id' (UNIQUE KEY))
		$query = "UPDATE `?p` SET ?u WHERE `task_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $task_id, 1);
	}

	// получаем список аналитики по всем звонкам
	public static function getAll(int $from_created_at, int $count, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX='get_all')
		$query = "SELECT * FROM `?p` WHERE `created_at` <= ?i ORDER BY `created_at` DESC LIMIT ?i OFFSET ?i";
		return ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, $from_created_at, $count, $offset);
	}

	// получаем список аналитики по всем звонкам с выборкой по пользователю
	public static function getAllByUserId(int $user_id, int $from_created_at, int $count, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX='get_all_by_user_id')
		$query = "SELECT * FROM `?p` USE INDEX (`?p`) WHERE `user_id` = ?i AND `created_at` <= ?i ORDER BY `created_at` DESC LIMIT ?i OFFSET ?i";
		return ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, "get_all_by_user_id", $user_id, $from_created_at, $count, $offset);
	}

	// получаем список аналитики по всем звонкам с выборкой по пользователю и номеру report_call_id
	public static function getAllByUserIdAndReportCallId(int $user_id, int $report_call_id):array {

		// запрос проверен на EXPLAIN (INDEX='get_by_user_id_report_call_id')
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `report_call_id` = ?i ORDER BY `created_at` DESC LIMIT ?i OFFSET ?i";
		return ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, $user_id, $report_call_id, 10, 0);
	}
}
