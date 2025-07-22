<?php

namespace Compass\Federation;

/**
 * Механизм автоматического обновления данных пользователя Compass, если его связанный аккаунт в LDAP обновили
 *
 * @event_category ldap
 * @event_name     account_updater
 */
class Type_Event_Ldap_ProfileUpdater {

	/** @var string тип события */
	public const EVENT_TYPE = "ldap.profile_updater";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_Ldap_ProfileUpdater::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Ldap_ProfileUpdater {

		return Struct_Event_Ldap_ProfileUpdater::build();
	}
}