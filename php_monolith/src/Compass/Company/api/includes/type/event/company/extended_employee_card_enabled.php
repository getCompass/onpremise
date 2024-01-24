<?php

namespace Compass\Company;

/**
 * Событие — включена расширенная карточка
 *
 * @event_category company
 * @event_name     extended_employee_card_enabled
 */
class Type_Event_Company_ExtendedEmployeeCardEnabled {

	/** @var string тип события */
	public const EVENT_TYPE = "company.extended_employee_card_enabled";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $creator_user_id, array $user_id_list):Struct_Event_Base {

		$event_data = Struct_Event_Company_ExtendedEmployeeCardEnabled::build($creator_user_id, $user_id_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Company_ExtendedEmployeeCardEnabled {

		return Struct_Event_Company_ExtendedEmployeeCardEnabled::build(...$event["event_data"]);
	}
}
