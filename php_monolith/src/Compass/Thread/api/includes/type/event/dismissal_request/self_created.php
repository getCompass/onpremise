<?php

namespace Compass\Thread;

/**
 * Событие — создание заявки на самоувольнение
 *
 * @event_name     created
 */
class Type_Event_DismissalRequest_SelfCreated {

	/** @var string тип события */
	public const EVENT_TYPE = "dismissal_request.self_created";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(array $dismissal_request):Struct_Event_Base {

		$event_data = Struct_Event_DismissalRequest_SelfCreated::build($dismissal_request);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_DismissalRequest_SelfCreated {

		return Struct_Event_DismissalRequest_SelfCreated::build(...$event["event_data"]);
	}
}
