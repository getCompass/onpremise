<?php

namespace Compass\Company;

/**
 * Событие — пользователь разлогинился из компании.
 *
 * @event_category user_company
 * @event_name     user_logout_company
 */
class Type_Event_UserCompany_UserLogoutCompany {

	/** @var string тип события */
	public const EVENT_TYPE = "user_company.user_logout_company";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, string $session_uniq):Struct_Event_Base {

		$event_data = Struct_Event_UserCompany_UserLogoutCompany::build($user_id, $session_uniq);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_UserCompany_UserLogoutCompany {

		return Struct_Event_UserCompany_UserLogoutCompany::build(...$event["event_data"]);
	}
}
