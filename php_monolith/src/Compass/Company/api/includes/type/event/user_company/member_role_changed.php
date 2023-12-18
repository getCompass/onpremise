<?php

namespace Compass\Company;

/**
 * Событие — смена роли участника компании
 *
 * @event_category user_company
 * @event_name     member_role_changed
 */
class Type_Event_UserCompany_MemberRoleChanged {

	/** @var string тип события */
	public const EVENT_TYPE = "user_company.member_role_changed";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 */
	public static function create(int $user_id, int $change_role_user_id, int $before_role, int $role):Struct_Event_Base {

		$event_data = Struct_Event_UserCompany_MemberRoleChanged::build($user_id, $change_role_user_id, $before_role, $role, time());
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_UserCompany_MemberRoleChanged {

		return Struct_Event_UserCompany_MemberRoleChanged::build(...$event["event_data"]);
	}
}
