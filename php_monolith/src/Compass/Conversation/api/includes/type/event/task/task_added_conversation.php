<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие — добавлена новая задача
 *
 * @event_category task
 * @event_name     task_added
 */
class Type_Event_Task_TaskAddedConversation {

	/** @var string тип события */
	public const EVENT_TYPE = "task.task_added_conversation";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param int   $task_type
	 * @param array $params
	 *
	 * @return Struct_Event_Base
	 * @throws ParseFatalException
	 */
	public static function create(int $task_type, array $params):Struct_Event_Base {

		$event_data = Struct_Event_Task_TaskAddedConversation::build($task_type, $params);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 */
	public static function parse(array $event):Struct_Event_Task_TaskAddedConversation {

		return Struct_Event_Task_TaskAddedConversation::build(...$event["event_data"]);
	}
}
