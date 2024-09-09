<?php

namespace Compass\Federation;

/**
 * класс для получения данных об учетной записи
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_AccountData implements Domain_Sso_Entity_CompassMapping_ParserInterface {

	/**
	 * получаем объект с основными данными об учетной записи
	 *
	 * @return Struct_Ldap_AccountData
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
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
}