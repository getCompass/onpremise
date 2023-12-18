<?php

namespace Compass\Conversation;

/**
 * Событие — имя сотрудника было изменено.
 *
 * @event_category member
 * @event_name     name_changed
 */
class Type_Event_Member_NameChanged {

	/** @var string тип события */
	public const EVENT_TYPE = "member.name_changed";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param int    $user_id
	 * @param string $full_name
	 *
	 * @return Struct_Event_Base
	 * @throws \parseException
	 */
	public static function create(int $user_id, string $full_name):Struct_Event_Base {

		$event_data = Struct_Event_Member_NameChanged::build($user_id, $full_name);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_Member_NameChanged
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Member_NameChanged {

		return Struct_Event_Member_NameChanged::build(...$event["event_data"]);
	}
}
