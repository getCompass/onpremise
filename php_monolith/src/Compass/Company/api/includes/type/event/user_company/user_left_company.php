<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие — пользователь покинул компанию.
 *
 * @event_category user_company
 * @event_name     user_left_company
 */
class Type_Event_UserCompany_UserLeftCompany {

	/** @var string тип события */
	public const EVENT_TYPE = "user_company.user_left_company";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Event_Base
	 * @throws ParseFatalException
	 */
	public static function create(int $user_id):Struct_Event_Base {

		$event_data = Struct_Event_UserCompany_UserLeftCompany::build($user_id);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_UserCompany_UserLeftCompany
	 * @throws ParseFatalException
	 */
	public static function parse(array $event):Struct_Event_UserCompany_UserLeftCompany {

		return Struct_Event_UserCompany_UserLeftCompany::build(...$event["event_data"]);
	}
}
