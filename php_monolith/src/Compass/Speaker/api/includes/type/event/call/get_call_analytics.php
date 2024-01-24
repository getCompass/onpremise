<?php

namespace Compass\Speaker;

/**
 * Событие — получить аналитику звонков
 *
 * @event_category call
 * @event_name     get_call_analytics
 */
class Type_Event_Call_GetCallAnalytics {

	/** @var string тип события */
	public const EVENT_TYPE = "call.get_call_analytics";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_Call_GetCallAnalytics::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function parse(array $event):Struct_Event_Call_GetCallAnalytics {

		return Struct_Event_Call_GetCallAnalytics::build();
	}
}
