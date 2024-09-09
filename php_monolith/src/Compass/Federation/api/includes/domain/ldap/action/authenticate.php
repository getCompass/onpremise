<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывающий действие попытки аутентификации в учетную запись LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Action_Authenticate {

	/** @var Domain_Ldap_Action_AuthenticateStrategy_Interface[] список существующих стратегий */
	protected const _AVAILABLE_STRATEGY_LIST = [
		Domain_Ldap_Action_AuthenticateStrategy_FilterDnSearch::class,
		Domain_Ldap_Action_AuthenticateStrategy_AttributeDnSearch::class,
		Domain_Ldap_Action_AuthenticateStrategy_DirectDnBinding::class,
	];

	/**
	 * @param string $username
	 * @param string $password
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function try(string $username, string $password):array {

		// определяем стратегию для аутентификации
		$strategy = self::_resolveStrategy();
		return $strategy->authenticate($username, $password);
	}

	/**
	 * определяем стратегию
	 *
	 * @return Domain_Ldap_Action_AuthenticateStrategy_Interface
	 * @throws ParseFatalException
	 */
	protected static function _resolveStrategy():Domain_Ldap_Action_AuthenticateStrategy_Interface {

		foreach (self::_AVAILABLE_STRATEGY_LIST as $strategy_class) {

			// получаем первую подходящую стратегию
			$strategy = new $strategy_class();
			if ($strategy->isActual()) {
				return $strategy;
			}
		}

		throw new ParseFatalException("cant resolve authenticate strategy");
	}
}