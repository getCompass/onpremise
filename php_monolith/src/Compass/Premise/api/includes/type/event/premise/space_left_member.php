<?php

namespace Compass\Premise;

/**
 * Событие — участник покинул команду
 *
 * @event_category premise
 * @event_name     space_left_member
 */
class Type_Event_Premise_SpaceLeftMember {

	/** @var string тип события */
	public const EVENT_TYPE = "premise.space_left_member";

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных
	 *
	 */
	public static function parse(array $event):Struct_Event_Premise_SpaceLeftMember {

		return Struct_Event_Premise_SpaceLeftMember::build(...$event["event_data"]);
	}
}
