<?php

namespace Compass\Premise;

/**
 * Событие — удаление профиля пользователя
 *
 * @event_category premise
 * @event_name     user_profile_deleted
 */
class Type_Event_Premise_UserProfileDeleted {

	/** @var string тип события */
	public const EVENT_TYPE = "premise.user_profile_deleted";

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных
	 *
	 */
	public static function parse(array $event):Struct_Event_Premise_UserProfileDeleted {

		return Struct_Event_Premise_UserProfileDeleted::build(...$event["event_data"]);
	}
}
