<?php

namespace Compass\Conversation;

/**
 * Событие — пользователь присоединился к компании.
 *
 * @event_category user_company
 * @event_name     user_joined_company
 */
class Type_Event_UserCompany_UserJoinedCompany {

	/** @var string тип события */
	public const EVENT_TYPE = "user_company.user_joined_company";

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_UserCompany_UserJoinedCompany {

		return Struct_Event_UserCompany_UserJoinedCompany::build(...$event["event_data"]);
	}
}
