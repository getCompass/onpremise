<?php

namespace Compass\FileNode;

/**
 * класс-интерфейс для работы с таблицей file_node.dlp_check_queue
 */
class Gateway_Db_FileNode_DlpCheckQueue extends Gateway_Db_FileNode_Main
{
	protected const _TABLE_KEY = "dlp_check_queue";

	/**
	 * Функция для добавления записи в таблицу
	 *
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_FileNode_DlpCheckQueue $dlp_check_queue_item): void
	{

		\sharding::pdo(self::_getDbKey())->insert(self::_TABLE_KEY, (array) $dlp_check_queue_item);
	}

	/**
	 * Получаем запись
	 */
	public static function get(int $queue_id): array
	{

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `queue_id` = ?i LIMIT ?i";
		return \sharding::pdo(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $queue_id, 1);
	}

	/**
	 * Получаем задачи для работы крона
	 */
	public static function getListForWork(int $limit, int $offset): array
	{

		// запрос проверен на EXPLAIN (INDEX=need_work)
		$query = "SELECT * FROM `?p` WHERE `need_work` < ?i LIMIT ?i OFFSET ?i";
		return \sharding::pdo(self::_getDbKey())->getAll($query, self::_TABLE_KEY, time(), $limit, $offset);
	}

	/**
	 * Получение количества записей
	 */
	public static function getTotalCount(int $file_type): int
	{

		// запрос проверен на EXPLAIN (INDEX=file_type)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `file_type` = ?i LIMIT ?i";
		$row   = \sharding::pdo(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $file_type, 1);
		return $row["count"];
	}

	/**
	 * Обновляем записи
	 */
	public static function updateList(array $queue_id_list, array $set): void
	{

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `queue_id` IN (?a) LIMIT ?i";
		\sharding::pdo(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $queue_id_list, count($queue_id_list));
	}

	/**
	 * Удаляем запись
	 */
	public static function delete(int $queue_id): void
	{

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `queue_id` = ?i LIMIT ?i";
		\sharding::pdo(self::_getDbKey())->delete($query, self::_TABLE_KEY, $queue_id, 1);
	}
}
