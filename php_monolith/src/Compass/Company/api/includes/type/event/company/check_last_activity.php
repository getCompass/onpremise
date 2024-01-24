<?php

namespace Compass\Company;

/**
 * Событие — для проверки последней активности
 *
 * @event_category company
 * @event_name     check_last_activity
 */
class Type_Event_Company_CheckLastActivity {

	/** @var string тип события */
	public const EVENT_TYPE = "company.check_last_activity";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_Company_CheckLastActivity::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_Company_CheckLastActivity
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Company_CheckLastActivity {

		return Struct_Event_Company_CheckLastActivity::build();
	}
}
