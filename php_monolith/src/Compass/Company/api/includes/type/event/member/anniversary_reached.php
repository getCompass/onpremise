<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Событие — в карточке что-то изменилось.
 *
 * @event_category member
 * @event_name     anniversary_reached
 */
class Type_Event_Member_AnniversaryReached {

	/** @var string тип события */
	public const EVENT_TYPE = "member.anniversary_reached";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $conversation_map, int $employee_user_id, array $editor_user_id_list, int $hired_at):Struct_Event_Base {

		$event_data = Struct_Event_Member_AnniversaryReached::build($conversation_map, $employee_user_id, $editor_user_id_list, $hired_at);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Member_AnniversaryReached {

		return Struct_Event_Member_AnniversaryReached::build(...$event["event_data"]);
	}
}
