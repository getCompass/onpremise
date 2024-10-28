<?php

namespace Compass\Federation;

use BaseFrame\Server\ServerProvider;

/**
 * класс для работы с клиентом LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Client {

	/**
	 * получаем класс клиента для работы с LDAP
	 *
	 * @return Domain_Ldap_Entity_Client_Interface
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function resolve(string $host, int $port, bool $use_ssl):Domain_Ldap_Entity_Client_Interface {

		if (ServerProvider::isTest()) {
			return new Domain_Ldap_Entity_Client_Mock();
		}

		return new Domain_Ldap_Entity_Client_Default($host, $port, $use_ssl);
	}
}