<?php

namespace Compass\Company;

/**
 * Событие — проверить не нужно ли запустить задачи наблюдателя пользователей
 *
 * @event_category user
 * @event_name     observer_check_required
 */
class Type_Event_User_ObserverCheckRequired {

	/** @var string тип события */
	public const EVENT_TYPE = "user.observer_check_required";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_User_ObserverCheckRequired::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_User_ObserverCheckRequired {

		return Struct_Event_User_ObserverCheckRequired::build();
	}
}
