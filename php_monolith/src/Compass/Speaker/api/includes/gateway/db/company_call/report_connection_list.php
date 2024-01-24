<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для таблицы company_call.report_connection_list
 */
class Gateway_Db_CompanyCall_ReportConnectionList extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_KEY = "report_connection_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// вставляем запись
	public static function insert(array $insert):void {

		ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	// получаем запись по report_id
	public static function getOne(int $report_id):array {

		$query = "SELECT * FROM `?p` WHERE `report_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $report_id, 1);
	}

	// получаем запись по call_map
	public static function getOneByCallMap(string $call_map):array {

		$query = "SELECT * FROM `?p` WHERE `call_map` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $call_map, 1);
	}

	// получить список жалоб
	public static function getList(int $user_id, mixed $status, int $from_created_at, int $count, int $offset):array {

		// формируем начало запроса и начальные аргументы
		// запрос проверен на EXPLAIN (INDEX='created_at')
		$query = "SELECT * FROM `?p` FORCE INDEX(`created_at`, `status_user_id`) WHERE `created_at` <= ?i ";
		$args  = [static::_TABLE_KEY, $from_created_at];

		// если передан идентификатор пользователя
		if ($user_id != 0) {

			$query  .= "AND `user_id` = ?i "; // запрос проверен на EXPLAIN (INDEX='created_at')
			$args[] = $user_id;
		}

		// если передан статус жалобы
		if ($status !== false) {

			$query  .= "AND `status` = ?i "; // запрос проверен на EXPLAIN (INDEX='status_user_id')
			$args[] = $status;
		}

		// добавляем завершающую часть запроса
		$query  .= "ORDER BY `report_id` DESC LIMIT ?i OFFSET ?i";
		$args[] = $count;
		$args[] = $offset;

		return self::_getList($query, $args);
	}

	// получаем список жалоб из базы
	protected static function _getList(string $query, array $args):array {

		// отправляем запрос и получаем список жалоб из таблицы
		$report_list = ShardingGateway::database(static::_getDbKey())->getAll($query, ...$args);

		// распаковываем extra каждой жалобы
		foreach ($report_list as $k => $_) {
			$report_list[$k]["extra"] = fromJson($report_list[$k]["extra"]);
		}

		return $report_list;
	}

	// обновить запись жалобы
	public static function set(int $report_call_id, array $set):void {

		$query = "UPDATE `?p` SET ?u WHERE `report_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $report_call_id, 1);
	}
}