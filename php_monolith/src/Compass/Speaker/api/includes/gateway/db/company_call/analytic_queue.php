<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для работы с таблицей company_call.analytic_queue
 */
class Gateway_Db_CompanyCall_AnalyticQueue extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_KEY = "analytic_queue";

	// создаем запись
	public static function insert(string $call_map, int $user_id):int {

		$insert = [
			"call_map"    => $call_map,
			"user_id"     => $user_id,
			"need_work"   => time(),
			"error_count" => 0,
			"created_at"  => time(),
		];
		return ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * Обновляем несколько записей
	 */
	public static function setList(array $task_id_list, array $set):void {

		$query = "UPDATE `?p` SET ?u WHERE `task_id` IN (?a) LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $task_id_list, count($task_id_list));
	}

	// удаляем записи
	public static function deleteList(array $task_id_list):void {

		$query = "DELETE FROM `?p` WHERE `task_id` IN (?a) LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->delete($query, self::_TABLE_KEY, $task_id_list, count($task_id_list));
	}

	// получаем список задач из базы, которые нужно выполнить
	public static function getTaskListWhichNeedWork(int $limit = 100, int $offset = 0):array {

		// запрос проверен на EXPLAIN (INDEX='cron_call_analytics')
		$query = "SELECT * FROM `?p` WHERE ?i = ?i ORDER BY `need_work` ASC LIMIT ?i OFFSET ?i";
		return ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, 1, 1, $limit, $offset);
	}
}