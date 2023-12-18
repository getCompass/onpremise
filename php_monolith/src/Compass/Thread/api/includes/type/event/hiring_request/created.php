<?php

namespace Compass\Thread;

/**
 * Событие — создание заявки
 *
 * @event_category hiring_request
 * @event_name     created
 */
class Type_Event_HiringRequest_Created {

	/** @var string тип события */
	public const EVENT_TYPE = "hiring_request.created";

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_HiringRequest_Created {

		return Struct_Event_HiringRequest_Created::build(...$event["event_data"]);
	}
}
