<?php

namespace Compass\Pivot;

/**
 * класс для работы с очередью на отправку писем
 * @package Compass\Pivot
 */
class Type_Mail_Queue {

	/** этапы отправки письма: */
	public const STAGE_SEND = 1;

	/**
	 * кол-во секунд в течение которых письми должно быть отправленным
	 * иначе считаем задачу на отправку письма проваленной
	 */
	protected const _EXPIRE_TASK_AFTER = 60;

	/**
	 * добавляем задачу на отправку письма
	 *
	 * @return Struct_Db_PivotMailService_Task
	 * @throws \queryException
	 */
	public static function addTask(string $message_id, string $mail, string $title, string $content, array $extra):Struct_Db_PivotMailService_Task {

		$task = new Struct_Db_PivotMailService_Task(
			message_id: $message_id,
			stage: self::STAGE_SEND,
			need_work: time(),
			error_count: 0,
			created_at_ms: timeMs(),
			updated_at: 0,
			task_expire_at: time() + self::_EXPIRE_TASK_AFTER,
			mail: $mail,
			title: $title,
			content: $content,
			extra: $extra,
		);
		Gateway_Db_PivotMailService_SendQueue::insert($task);

		return $task;
	}

	/**
	 * Функция для получения задачь из базы
	 * @return Struct_Db_PivotMailService_Task[]
	 */
	public static function getList(int $bot_num, int $count):array {

		$offset = $bot_num * $count;
		return Gateway_Db_PivotMailService_SendQueue::getCronTaskList($count, $offset);
	}

	/**
	 * Обновляем список задач после того как они были взяты в работу
	 */
	public static function updateTaskList(array $message_id_list, int $need_work_interval):void {

		Gateway_Db_PivotMailService_SendQueue::updateList($message_id_list, [
			"need_work"   => time() + $need_work_interval,
			"error_count" => "error_count + 1",
		]);
	}

	/**
	 * Удаляем задачу
	 */
	public static function deleteTask(string $message_id):void {

		Gateway_Db_PivotMailService_SendQueue::delete($message_id);
	}

}