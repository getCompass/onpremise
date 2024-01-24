<?php

namespace Compass\Userbot;

/**
 * Интерфейс для работы с таблицк userbot_main.request_list
 */
class Gateway_Db_UserbotMain_RequestList extends Gateway_Db_UserbotMain_Main {

	protected const _TABLE_KEY = "request_list";

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 * @throws \cs_RowDuplication
	 */
	public static function insert(Struct_Db_UserbotMain_Request $request):void {

		$insert = [
			"request_id"   => $request->request_id,
			"token"        => $request->token,
			"status"       => $request->status,
			"error_count"  => $request->error_count,
			"need_work"    => $request->need_work,
			"created_at"   => $request->created_at,
			"updated_at"   => $request->updated_at,
			"request_data" => $request->request_data,
			"result_data"  => $request->result_data,
		];

		try {
			ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert, false);
		} catch (\PDOException $e) {

			// если это дупликат
			if ($e->getCode() == 23000) {
				throw new \cs_RowDuplication();
			}

			throw $e;
		}
	}

	/**
	 * получаем запись
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(string $request_id, string $token):Struct_Db_UserbotMain_Request {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `request_id` = ?s AND `token` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $request_id, $token, 1);

		if (!isset($row["request_id"])) {
			throw new \cs_RowIsEmpty("not found request");
		}

		return self::_rowToObject($row);
	}

	/**
	 * получаем несколько записей
	 */
	public static function getList(int $limit, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX=`get_by_need_work`)
		$query = "SELECT * FROM `?p` WHERE `need_work` <= ?i ORDER BY `need_work` ASC LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, time(), $limit, $offset);

		$obj_list = [];
		foreach ($list as $row) {
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * Получение количества записей
	 *
	 * @return int
	 */
	public static function getTotalCount():int {

		// запрос проверен на EXPLAIN (INDEX=get_by_need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param int $need_work
	 *
	 * @return int
	 */
	public static function getExpiredCount(int $need_work):int {

		// запрос проверен на EXPLAIN (INDEX=get_by_need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `need_work` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $need_work, 1);
		return $row["count"];
	}

	/**
	 * обновляем записи
	 */
	public static function updateList(array $request_id_list, array $token_list, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `request_id` IN (?a) AND `token` IN (?a) LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $request_id_list, $token_list, count($request_id_list));
	}

	/**
	 * удаляем записи
	 */
	public static function deleteList(array $request_id_list, array $token_list):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `request_id` IN (?a) AND `token` IN (?a) LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->delete($query, self::_TABLE_KEY, $request_id_list, $token_list, count($request_id_list));
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * преобразуем строку записи в объект
	 */
	protected static function _rowToObject(array $row):Struct_Db_UserbotMain_Request {

		return new Struct_Db_UserbotMain_Request(
			$row["request_id"],
			$row["token"],
			$row["status"],
			$row["error_count"],
			$row["need_work"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["request_data"]),
			fromJson($row["result_data"]),
		);
	}
}
