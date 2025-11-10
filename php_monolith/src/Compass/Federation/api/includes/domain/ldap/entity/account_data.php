<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для получения данных об учетной записи
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_AccountData implements Domain_Sso_Entity_CompassMapping_ParserInterface {

    /**
     * получаем объект с основными данными об учетной записи
     *
     * @param array $ldap_account_entry
     * @param string $username
     * @return Struct_Ldap_AccountData
     * @throws ParseFatalException
     */
	public static function parse(array $ldap_account_entry, string $username):Struct_Ldap_AccountData {

		// объект парсера
		$parser = new self();

		return new Struct_Ldap_AccountData(
			account_data: new Struct_Sso_AccountData(
				name: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_NAME, $ldap_account_entry, $parser),
				avatar: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_AVATAR, $ldap_account_entry, $parser),
				badge: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_BADGE, $ldap_account_entry, $parser),
				role: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_ROLE, $ldap_account_entry, $parser),
				bio: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_BIO, $ldap_account_entry, $parser),
			),
			uid: Domain_Ldap_Entity_Utils::getUniqueAttributeValue($ldap_account_entry, Domain_Ldap_Entity_Config::getUserUniqueAttribute()),
			username: $username,
		);
	}

	/**
	 * @return string
	 */
	public static function parseField(mixed $data, string $attribute):string {

		// переводим в нижний регистр, из-за особенностей ldap
		$attribute = mb_strtolower($attribute);
		return Domain_Ldap_Entity_Utils::getAttribute($data, $attribute);
	}

	/**
	 * Спарсить выражение присваивания
	 *
	 * @param mixed  $data
	 * @param string $assignment
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function parseAssignment(mixed $data, string $assignment):array {


		// ищем выражение вида manager:distinguishedName='{manager}' где
		// manager - ключ, к которому будет присвоен сторонний объект LDAP
		// distinguishedName - атрибут, по которому будем искать в LDAP
		// '{manager}' - значение атрибута, по которому ищем в LDAP. Значение может состоять из нескольких {атрибутов} текущего пользователя
		preg_match("/(\w+):(\w+)='([\s\S]+)'/", $assignment, $matches);

		if (count($matches) === 0) {
			return [];
		}

		$assign_key            = trim($matches[1], " \n\r\t\v\0'");
		$ldap_unique_attribute = trim($matches[2], " \n\r\t\v\0'");
		$ldap_search_key       = trim($matches[3], " \n\r\t\v\0'");

		// убираем экранирование ', которое мог оставить пользователь
		$ldap_search_key = str_replace("\'", "'", $ldap_search_key);

		// парсим значение атрибута для поиска, подставляя значения от текущего пользователя
		$ldap_search_value = Domain_Sso_Entity_CompassMapping_Parser::parseFieldContent($ldap_search_key, $data, new self());

		// ищем стороннего пользователя
		try {
			$search_entity_data = Domain_Ldap_Action_Search::do($ldap_unique_attribute, $ldap_search_value);
		} catch (Domain_Ldap_Exception_ProtocolError_InvalidCredentials) {
			return [];
		}

		return [$assign_key => $search_entity_data];
	}
}