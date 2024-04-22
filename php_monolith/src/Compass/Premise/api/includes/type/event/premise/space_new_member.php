<?php

namespace Compass\Premise;

/**
 * Событие — вступления участника в команду
 *
 * @event_category premise
 * @event_name     space_new_member
 */
class Type_Event_Premise_SpaceNewMember {

	/** @var string тип события */
	public const EVENT_TYPE = "premise.space_new_member";

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных
	 *
	 */
	public static function parse(array $event):Struct_Event_Premise_SpaceNewMember {

		return Struct_Event_Premise_SpaceNewMember::build(...$event["event_data"]);
	}
}
