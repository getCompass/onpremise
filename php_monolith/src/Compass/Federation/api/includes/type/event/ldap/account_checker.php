<?php

namespace Compass\Federation;

/**
 * Механизм автоматической блокировки пользователя Compass, если его связанный аккаунт в LDAP был удален или заблокирован
 *
 * @event_category ldap
 * @event_name     account_checker
 */
class Type_Event_Ldap_AccountChecker {

	/** @var string тип события */
	public const EVENT_TYPE = "ldap.account_checker";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_Ldap_AccountChecker::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Ldap_AccountChecker {

		return Struct_Event_Ldap_AccountChecker::build();
	}
}
