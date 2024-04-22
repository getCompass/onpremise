<?php

namespace Compass\Premise;

/**
 * Событие — изменение роли/прав пользователя в команде
 *
 * @event_category premise
 * @event_name     space_changed_member
 */
class Type_Event_Premise_SpaceChangedMember {

	/** @var string тип события */
	public const EVENT_TYPE = "premise.space_changed_member";

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных
	 *
	 */
	public static function parse(array $event):Struct_Event_Premise_SpaceChangedMember {

		return Struct_Event_Premise_SpaceChangedMember::build(...$event["event_data"]);
	}
}
