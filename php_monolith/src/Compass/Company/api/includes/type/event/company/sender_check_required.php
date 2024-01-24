<?php

namespace Compass\Company;

/**
 * Событие — проверить не нужно ли запустить задачи по периодической рассылке данных для компаний
 *
 * @event_category company
 * @event_name     sender_check_required
 */
class Type_Event_Company_SenderCheckRequired {

	/** @var string тип события */
	public const EVENT_TYPE = "company.sender_check_required";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_Company_SenderCheckRequired::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Company_SenderCheckRequired {

		return Struct_Event_Company_SenderCheckRequired::build();
	}
}
