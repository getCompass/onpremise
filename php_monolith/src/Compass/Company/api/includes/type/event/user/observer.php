<?php

namespace Compass\Company;

/**
 * Событие — наблюдатель за пользователем
 *
 * @event_category user
 * @event_name     observer
 */
class Type_Event_User_Observer {

	/** @var string тип события */
	public const EVENT_TYPE = "user.observer";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(array $observer_job_list):Struct_Event_Base {

		$event_data = Struct_Event_User_Observer::build($observer_job_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_User_Observer {

		return Struct_Event_User_Observer::build(...$event["event_data"]);
	}
}
