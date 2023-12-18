<?php

namespace Compass\Company;

/**
 * Событие — смена permissions участника компании
 *
 * @event_category user_company
 * @event_name     member_permissions_changed
 */
class Type_Event_Member_PermissionsChanged {

	/** @var string тип события */
	public const EVENT_TYPE = "member.permissions_changed";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, int $before_role, int $before_permissions, int $role, int $permissions):Struct_Event_Base {

		$event_data = Struct_Event_Member_PermissionsChanged::build($user_id, $before_role, $before_permissions, $role, $permissions, time());
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Member_PermissionsChanged {

		return Struct_Event_Member_PermissionsChanged::build(...$event["event_data"]);
	}
}
