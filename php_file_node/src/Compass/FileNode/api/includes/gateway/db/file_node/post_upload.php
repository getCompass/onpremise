<?php

namespace Compass\FileNode;

/**
 * класс-интерфейс для работы с таблицей file_node.post_upload_queue
 */
class Gateway_Db_FileNode_PostUpload extends Gateway_Db_FileNode_Main {

	protected const _TABLE_KEY = "post_upload_queue";

	/**
	 * Функция для добавления записи в таблицу
	 *
	 * @param array $insert
	 *
	 * @return void
	 * @throws \queryException
	 */
	public static function insert(array $insert):void {

		\sharding::pdo(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * Функция для добавления записей в таблицу
	 *
	 * @param array $insert
	 *
	 * @return void
	 */
	public static function insertArray(array $insert):void {

		\sharding::pdo(self::_getDbKey())->insertArray(self::_TABLE_KEY, $insert);
	}

	/**
	 * Получаем запись
	 *
	 * @param int $queue_id
	 *
	 * @return array
	 */
	public static function get(int $queue_id):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `queue_id` = ?i LIMIT ?i";
		return \sharding::pdo(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $queue_id, 1);
	}

	/**
	 * Получаем задачи для работы крона
	 *
	 * @param int $file_type
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public static function getListForWork(int $file_type, int $limit, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX=cron_postupload)
		$query = "SELECT * FROM `?p` FORCE INDEX (`cron_postupload`) WHERE `need_work` < ?i AND `file_type` = ?i LIMIT ?i OFFSET ?i";
		return \sharding::pdo(self::_getDbKey())->getAll($query, self::_TABLE_KEY, time(), $file_type, $limit, $offset);
	}

	/**
	 * Получение количества записей
	 *
	 * @param int $file_type
	 *
	 * @return int
	 */
	public static function getTotalCount(int $file_type):int {

		// запрос проверен на EXPLAIN (INDEX=type)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `file_type` = ?i LIMIT ?i";
		$row   = \sharding::pdo(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $file_type, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param int $need_work
	 * @param int $file_type
	 *
	 * @return int
	 */
	public static function getExpiredCount(int $need_work, int $file_type):int {

		// запрос проверен на EXPLAIN (INDEX=cron_postupload)
		$query = "SELECT COUNT(*) as `count` FROM `?p` FORCE INDEX (`cron_postupload`) WHERE `need_work` < ?i AND `file_type` = ?i LIMIT ?i";
		$row   = \sharding::pdo(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $need_work, $file_type, 1);
		return $row["count"];
	}

	/**
	 * Обновляем записи
	 *
	 * @param array $queue_id_list
	 * @param array $set
	 *
	 * @return void
	 */
	public static function updateList(array $queue_id_list, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `queue_id` IN (?a) LIMIT ?i";
		\sharding::pdo(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $queue_id_list, count($queue_id_list));
	}

	/**
	 * Удаляем запись
	 *
	 * @param int $queue_id
	 *
	 * @return void
	 */
	public static function delete(int $queue_id):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `queue_id` = ?i LIMIT ?i";
		\sharding::pdo(self::_getDbKey())->delete($query, self::_TABLE_KEY, $queue_id, 1);
	}
}