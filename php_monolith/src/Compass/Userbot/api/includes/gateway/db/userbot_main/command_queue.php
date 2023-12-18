<?php

namespace Compass\Userbot;

/**
 * Интерфейс для работы с таблицк userbot_main.command_queue
 */
class Gateway_Db_UserbotMain_CommandQueue extends Gateway_Db_UserbotMain_Main {

	protected const _TABLE_KEY = "command_queue";

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_UserbotMain_Command $command):void {

		$insert = [
			"error_count" => $command->error_count,
			"need_work"   => $command->need_work,
			"created_at"  => $command->created_at,
			"params"      => $command->params,
		];

		ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert, false);
	}

	/**
	 * получаем запись
	 */
	public static function get(int $task_id):Struct_Db_UserbotMain_Command {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `task_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $task_id, 1);

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
	public static function updateList(array $task_id_list, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `task_id` IN (?a) LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $task_id_list, count($task_id_list));
	}

	/**
	 * удаляем записи
	 */
	public static function deleteList(array $task_id_list):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `task_id` IN (?a) LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->delete($query, self::_TABLE_KEY, $task_id_list, count($task_id_list));
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * преобразуем строку записи в объект
	 */
	protected static function _rowToObject(array $row):Struct_Db_UserbotMain_Command {

		return new Struct_Db_UserbotMain_Command(
			$row["task_id"],
			$row["error_count"],
			$row["need_work"],
			$row["created_at"],
			fromJson($row["params"]),
		);
	}
}
