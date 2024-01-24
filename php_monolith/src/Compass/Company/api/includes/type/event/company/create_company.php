<?php

namespace Compass\Company;

/**
 * Событие — для создания компании
 *
 * @event_category company
 * @event_name     check_last_activity
 */
class Type_Event_Company_CreateCompany {

	/** @var string тип события */
	public const EVENT_TYPE = "company.create_company";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $creator_user_id, array $bot_list, int $is_enabled_employee_card):Struct_Event_Base {

		$event_data = Struct_Event_Company_CreateCompany::build(
			$creator_user_id, $bot_list, $is_enabled_employee_card
		);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_Company_CreateCompany
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Company_CreateCompany {

		return Struct_Event_Company_CreateCompany::build(...$event["event_data"]);
	}
}
