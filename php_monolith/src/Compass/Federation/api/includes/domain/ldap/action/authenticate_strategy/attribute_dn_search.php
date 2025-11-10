<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * стратегия аутентфикиации:
 * На основе уникального атрибута учетной записи ldap.user_unique_attribute (например uid)
 * с помощью учетной записи ldap.user_search_account_dn : ldap.user_search_account_password
 * пытаемся найти DN целевой учетной записи, в которую хотят авторизоваться
 */
class Domain_Ldap_Action_AuthenticateStrategy_AttributeDnSearch implements Domain_Ldap_Action_AuthenticateStrategy_Interface {

	public function isActual():bool {

		return Domain_Ldap_Entity_Config::getUserLoginAttribute() !== ""
			&& Domain_Ldap_Entity_Config::getUserSearchBase() !== ""
			&& Domain_Ldap_Entity_Config::getUserSearchAccountDn() !== ""
			&& Domain_Ldap_Entity_Config::getUserSearchAccountPassword() !== ""

			// стратегия используется только если не заполнен ldap.user_search_filter
			&& Domain_Ldap_Entity_Config::getUserSearchFilter() == "";
	}

	public function authenticate(string $username, string $password):array {

		// создаем клиент
		$client = Domain_Ldap_Entity_Client::resolve(
			Domain_Ldap_Entity_Config::getServerHost(),
			Domain_Ldap_Entity_Config::getServerPort(),
			Domain_Ldap_Entity_Config::getUseSslFlag(),
			Domain_Ldap_Entity_Client_RequireCertStrategy::convertStringToConst(Domain_Ldap_Entity_Config::getRequireCertStrategy()),
		);

		// пытаемся авторизоваться из под аккаунта для поиска
		$client->bind(Domain_Ldap_Entity_Config::getUserSearchAccountDn(), Domain_Ldap_Entity_Config::getUserSearchAccountPassword());

		// пытаемся найти DN целевой учетной записи, в которую пытаются авторизоваться
		$search_filter = Domain_Ldap_Entity_Utils::formatUserFilterByUniqueAttribute(Domain_Ldap_Entity_Config::getUserLoginAttribute(), $username);
		[$count, $entry_list] = $client->searchEntries(Domain_Ldap_Entity_Config::getUserSearchBase(), $search_filter, 1);

		// если не удалось ничего найти
		if ($count < 1) {
			throw new Domain_Ldap_Exception_ProtocolError_InvalidCredentials();
		}

		// получили DN целевой учетной записи и пытаемся финально авторизоваться
		$dn = Domain_Ldap_Entity_Utils::getDnAttribute($entry_list[0]);
		$client->bind($dn, $password);

		// получаем информацию об учетной записе
		[$count, $entry_list] = $client->searchEntries(Domain_Ldap_Entity_Config::getUserSearchBase(), $search_filter, 1);

		// закрываем соединение
		$client->unbind();

		// проверяем наличие результатов
		if ($count === 0) {
			throw new ParseFatalException("unexpected behaviour, account not found");
		}

		return $entry_list[0];
	}
}