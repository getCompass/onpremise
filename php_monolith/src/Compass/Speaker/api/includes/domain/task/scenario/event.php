<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Агрегатор подписок на событие для домена task.
 */
class Domain_Task_Scenario_Event {

	/**
	 * Callback для события добавления задачи
	 *
	 * @param Struct_Event_Task_TaskAddedSpeaker $event_data
	 *
	 * @throws \parseException
	 */
	#[Type_Attribute_EventListener(Type_Event_Task_TaskAddedSpeaker::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onTaskAddedSpeaker(Struct_Event_Task_TaskAddedSpeaker $event_data):void {

		$php_hooker = new Type_Phphooker_Worker();
		$php_hooker->doTask($event_data->task_type, $event_data->params);
	}
}