<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие — компания пробудилась ото сна
 *
 * @event_category company
 * @event_name     on_wake_up
 */
class Type_Event_Company_OnWakeUp {

	/** @var string тип события */
	public const EVENT_TYPE = "company.on_wake_up";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws ParseFatalException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_Company_OnWakeUp::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws ParseFatalException
	 */
	public static function parse(array $event):Struct_Event_Company_OnWakeUp {

		return Struct_Event_Company_OnWakeUp::build();
	}
}
