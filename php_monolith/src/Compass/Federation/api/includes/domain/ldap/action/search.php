<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывающий действие поиска сущности в LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Action_Search {

	/**
	 * Выполняем
	 *
	 * @param string $unique_search_attribute
	 * @param string $attribute_value
	 *
	 * @return array
	 * @throws Domain_Ldap_Exception_ProtocolError_InvalidCredentials
	 * @throws ParseFatalException
	 */
	public static function do(string $unique_search_attribute, string $attribute_value):array {

		// создаем клиент
		$client = Domain_Ldap_Entity_Client::resolve(
			Domain_Ldap_Entity_Config::getServerHost(),
			Domain_Ldap_Entity_Config::getServerPort(),
			Domain_Ldap_Entity_Config::getUseSslFlag(),
			Domain_Ldap_Entity_Client_RequireCertStrategy::convertStringToConst(Domain_Ldap_Entity_Config::getRequireCertStrategy()),
		);

		// пытаемся авторизоваться из под аккаунта для поиска
		$client->bind(Domain_Ldap_Entity_Config::getUserSearchAccountDn(), Domain_Ldap_Entity_Config::getUserSearchAccountPassword());

		// пытаемся найти DN целевой учетной записи
		$search_filter = Domain_Ldap_Entity_Utils::formatUserFilterByUniqueAttribute($unique_search_attribute, $attribute_value);
		[$count, $entry_list] = $client->searchEntries(Domain_Ldap_Entity_Config::getUserSearchBase(), $search_filter, 1);

		// если не удалось ничего найти
		if ($count < 1) {
			throw new Domain_Ldap_Exception_ProtocolError_InvalidCredentials();
		}

		return Domain_Ldap_Entity_Utils::prepareEntry($entry_list[0]);
	}
}