<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы со значениями опции LDAP_OPT_X_TLS_REQUIRE_CERT
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Client_RequireCertStrategy {

	/**
	 * конвертируем строку с require cert стратегией в predefined constants
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function convertStringToConst(string $require_cert_strategy):int {

		return match ($require_cert_strategy) {
			"never"  => LDAP_OPT_X_TLS_NEVER,
			"allow"  => LDAP_OPT_X_TLS_ALLOW,
			"try"    => LDAP_OPT_X_TLS_TRY,
			"demand" => LDAP_OPT_X_TLS_DEMAND,
			"hard"   => LDAP_OPT_X_TLS_HARD,
			default  => throw new ParseFatalException("Unexpected require cert strategy: $require_cert_strategy"),
		};
	}
}