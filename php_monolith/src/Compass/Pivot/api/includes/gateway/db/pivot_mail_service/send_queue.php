<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для работы с таблицей pivot_mail_service.send_queue
 */
class Gateway_Db_PivotMailService_SendQueue extends Gateway_Db_PivotMailService_Main {

	protected const _TABLE_KEY = "send_queue";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Создать запись
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotMailService_Task $task):void {

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, [
			"message_id"     => $task->message_id,
			"stage"          => $task->stage,
			"need_work"      => $task->need_work,
			"error_count"    => $task->error_count,
			"created_at_ms"  => $task->created_at_ms,
			"updated_at"     => $task->updated_at,
			"task_expire_at" => $task->task_expire_at,
			"mail"           => $task->mail,
			"title"          => $task->title,
			"content"        => $task->content,
			"extra"          => $task->extra,
		], false);
	}

	/**
	 * Обновляем запись
	 */
	public static function update(string $message_id, array $set):int {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `message_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $message_id, 1);
	}

	/**
	 * Получаем запись
	 */
	public static function getOne(string $message_id):null|Struct_Db_PivotMailService_Task {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `message_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $message_id, 1);

		if (!isset($row["message_id"])) {
			return null;
		}

		return Struct_Db_PivotMailService_Task::rowToStruct($row);
	}

	/**
	 * получаем список задач для крона
	 *
	 * @return Struct_Db_PivotMailService_Task[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getCronTaskList(int $count, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX=cron_mail_dispatcher)
		$query = "SELECT * FROM `?p` WHERE need_work <= ?i LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, time(), $count, $offset);

		return array_map(static fn(array $row) => Struct_Db_PivotMailService_Task::rowToStruct($row), $list);
	}

	/**
	 * Обновляем список записей
	 */
	public static function updateList(array $message_id_list, array $set):int {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		return ShardingGateway::database(self::_DB_KEY)
			->update("UPDATE `?p` SET ?u WHERE `message_id` IN (?a) LIMIT ?i", self::_TABLE_KEY, $set, $message_id_list, count($message_id_list));
	}

	/**
	 * удаляем задачу
	 */
	public static function delete(string $message_id):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_DB_KEY)->delete("DELETE FROM `?p` WHERE `message_id` = ?s LIMIT ?i", self::_TABLE_KEY, $message_id, 1);
	}
}