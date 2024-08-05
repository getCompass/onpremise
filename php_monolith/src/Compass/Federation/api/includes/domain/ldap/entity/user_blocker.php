<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с блокировкой пользователя Compass
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_UserBlocker {

	/**
	 * получаем объект класса, который будет блокировать пользователя Compass в зависимости от уровня блокировки
	 *
	 * @return Domain_Ldap_Entity_UserBlocker_Abstract
	 * @throws ParseFatalException
	 */
	public static function resolveBlocker(Domain_Ldap_Entity_UserBlocker_Level $level):Domain_Ldap_Entity_UserBlocker_Abstract {

		/** @noinspection PhpUnusedMatchConditionInspection */
		return match ($level) {
			Domain_Ldap_Entity_UserBlocker_Level::LIGHT => new Domain_Ldap_Entity_UserBlocker_Light(),
			Domain_Ldap_Entity_UserBlocker_Level::HARD  => new Domain_Ldap_Entity_UserBlocker_Hard(),
			default                                     => throw new ParseFatalException("unexpected level [$level->value]"),
		};
	}
}