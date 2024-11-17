<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * стратегия аутентфикиации:
 * На основе уникального атрибута учетной записи ldap.user_unique_attribute (например uid) и ldap.user_search_base
 * пытаемся сформировать DN учетной записи, в которую намереваются авторизоваться
 */
class Domain_Ldap_Action_AuthenticateStrategy_DirectDnBinding implements Domain_Ldap_Action_AuthenticateStrategy_Interface {

	public function isActual():bool {

		return Domain_Ldap_Entity_Config::getUserUniqueAttribute() !== "" && Domain_Ldap_Entity_Config::getUserSearchBase() !== "";
	}

	public function authenticate(string $username, string $password):array {

		// создаем клиент
		$client = Domain_Ldap_Entity_Client::resolve(
			Domain_Ldap_Entity_Config::getServerHost(),
			Domain_Ldap_Entity_Config::getServerPort(),
			Domain_Ldap_Entity_Config::getUseSslFlag(),
			Domain_Ldap_Entity_Client_RequireCertStrategy::convertStringToConst(Domain_Ldap_Entity_Config::getRequireCertStrategy()),
		);

		// пытаемся аутентифицироваться с помощью ldap_bind сформировав DN учетной записи
		$client->bind(self::_makeUserDN($username), $password);

		// получаем информацию об учетной записе
		[$count, $entry_list] = $client->searchEntries(Domain_Ldap_Entity_Config::getUserSearchBase(), Domain_Ldap_Entity_Utils::formatUserFilterByUniqueAttribute(Domain_Ldap_Entity_Config::getUserUniqueAttribute(), $username), 1);

		// закрываем соединение
		$client->unbind();

		// проверяем наличие результатов
		if ($count === 0) {
			throw new ParseFatalException("unexpected behaviour, account not found");
		}

		return $entry_list[0];
	}

	/** формируем user DN */
	protected static function _makeUserDN(string $username):string {

		return sprintf("%s=%s,%s", Domain_Ldap_Entity_Config::getUserUniqueAttribute(), $username, Domain_Ldap_Entity_Config::getUserSearchBase());
	}
}