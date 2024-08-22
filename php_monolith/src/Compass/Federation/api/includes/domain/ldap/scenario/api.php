<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;

/**
 * класс содержит логику аутентификации по протоколу LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Scenario_Api {

	/**
	 * пытаемся аутентифицировать учетную запись в LDAP
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return string
	 * @throws BlockException
	 * @throws \queryException
	 * @throws Domain_Ldap_Exception_ProtocolError
	 * @throws ParseFatalException
	 */
	public static function tryAuthenticate(string $username, string $password):string {

		// проверяем, достигнут ли лимит неудачных попыток аутентификации для этого IP адреса
		$antispam_block_key = Type_Antispam_Ip::overrideBlockKeyLimit(Type_Antispam_Ip::LDAP_FAILED_TRY_AUTHENTICATE, Domain_Ldap_Entity_Config::getLimitOfIncorrectAuthAttempts());
		Type_Antispam_Ip::check($antispam_block_key);

		try {
			$entry = Domain_Ldap_Action_Authenticate::try($username, $password);
		} catch (Domain_Ldap_Exception_ProtocolError $e) {

			Type_Antispam_Ip::checkAndIncrementBlock($antispam_block_key);

			// сохраняем провальную попытку
			Domain_Ldap_Entity_AuthToken::save("", $username, "", Domain_Ldap_Entity_AuthToken::STATUS_LDAP_AUTH_FAILED, []);
			Domain_Ldap_Entity_Logger::log("Неудачная попытка аутентификации [username: $username]", ["error_num" => $e->getErrorNumber(), "message" => $e->getMessage()]);

			throw $e;
		}

		$prepared_entry = Domain_Ldap_Entity_Utils::prepareEntry($entry);
		[$user_unique_attribute_value, $dn] = Domain_Ldap_Entity_Utils::parseEntryAttributes($prepared_entry, Domain_Ldap_Entity_Config::getUserUniqueAttribute());

		// сохраняем попытку аутентификации
		$auth_token_data = Domain_Ldap_Entity_AuthToken_Data::initData();
		$auth_token_data = Domain_Ldap_Entity_AuthToken_Data::setEntry($auth_token_data, $entry);
		$auth_token      = Domain_Ldap_Entity_AuthToken::save($user_unique_attribute_value, $username, $dn, Domain_Ldap_Entity_AuthToken::STATUS_LDAP_AUTH_COMPLETE, $auth_token_data);

		return $auth_token->ldap_auth_token;
	}
}