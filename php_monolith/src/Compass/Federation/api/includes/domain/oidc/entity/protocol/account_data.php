<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для получения данных об SSO аккаунте
 * @package Compass\Federation
 */
class Domain_Oidc_Entity_Protocol_AccountData implements Domain_Sso_Entity_CompassMapping_ParserInterface {

	/**
	 * получаем объект с основными данными об аккаунте SSO
	 *
	 * @param Struct_Db_SsoData_SsoAccountOidcToken $account_oidc_token
	 *
	 * @return Struct_Oidc_AccountData
	 * @throws ParseFatalException
	 */
	public static function get(Struct_Db_SsoData_SsoAccountOidcToken $account_oidc_token):Struct_Oidc_AccountData {

		// получаем id_token
		$id_token = $account_oidc_token->data->id_token;

		// объект парсера
		$parser = new self();

		// достаем mail/phone_number учетной записи
		$mapped_attribute_mail         = Domain_Oidc_Entity_Protocol_Config::getMappedAttributeName(Domain_Oidc_Entity_Protocol_Config::MAPPED_ATTRIBUTE_MAIL);
		$mail                          = $account_oidc_token->data->user_info_data[$mapped_attribute_mail] ?? Domain_Oidc_Entity_Protocol_Token::getIdTokenCustomFieldValue($id_token, $mapped_attribute_mail);
		$mapped_attribute_phone_number = Domain_Oidc_Entity_Protocol_Config::getMappedAttributeName(Domain_Oidc_Entity_Protocol_Config::MAPPED_ATTRIBUTE_PHONE_NUMBER);
		$phone_number                  = $account_oidc_token->data->user_info_data[$mapped_attribute_phone_number] ?? Domain_Oidc_Entity_Protocol_Token::getIdTokenCustomFieldValue($id_token, $mapped_attribute_phone_number);

		// собираем объект с данными об учетной записи
		return new Struct_Oidc_AccountData(
			account_data: new Struct_Sso_AccountData(
				name: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_NAME, $id_token, $parser),
				avatar: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_AVATAR, $id_token, $parser),
				badge: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_BADGE, $id_token, $parser),
				role: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_ROLE, $id_token, $parser),
				bio: Domain_Sso_Entity_CompassMapping_Parser::parseField(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_BIO, $id_token, $parser),
			),
			mail: $mail,
			phone_number: $phone_number,
		);
	}

	/**
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function parseField(mixed $data, string $attribute):string {

		return Domain_Oidc_Entity_Protocol_Token::getIdTokenCustomFieldValue($data, $attribute);
	}

	/**
	 * Спарсить выражение
	 *
	 * @param mixed  $data
	 * @param string $expression
	 *
	 * @return string
	 */
	public static function parseAssignment(mixed $data, string $assignment):array {

		return [];
	}
}