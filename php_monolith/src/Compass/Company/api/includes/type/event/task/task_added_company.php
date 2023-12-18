<?php

namespace Compass\Company;

/**
 * Событие — добавлена новая задача
 *
 * @event_category task
 * @event_name     task_added
 */
class Type_Event_Task_TaskAddedCompany {

	/** @var string тип события */
	public const EVENT_TYPE = "task.task_added_company";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $task_type, array $params):Struct_Event_Base {

		$event_data = Struct_Event_Task_TaskAddedCompany::build($task_type, $params);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Task_TaskAddedCompany {

		return Struct_Event_Task_TaskAddedCompany::build(...$event["event_data"]);
	}
}
