<?php

namespace Compass\Company;

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
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(array $hiring_request):Struct_Event_Base {

		$event_data = Struct_Event_HiringRequest_Created::build($hiring_request);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

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
