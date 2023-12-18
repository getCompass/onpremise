<?php

namespace Compass\Company;

/**
 * Событие — обновления информации списка пользователей
 *
 * @event_category user_company
 * @event_name     update_user_info_list
 */
class Type_Event_UserCompany_UpdateUserInfoList {

	/** @var string тип события */
	public const EVENT_TYPE = "user_company.update_user_info_list";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param array $user_info_list
	 *
	 * @return Struct_Event_Base
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function create(array $user_info_list):Struct_Event_Base {

		$event_data = Struct_Event_UserCompany_UpdateUserInfoList::build($user_info_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_UserCompany_UpdateUserInfoList
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function parse(array $event):Struct_Event_UserCompany_UpdateUserInfoList {

		return Struct_Event_UserCompany_UpdateUserInfoList::build(...$event["event_data"]);
	}
}
