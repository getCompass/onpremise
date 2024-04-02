<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для получения данных об SSO аккаунте
 * @package Compass\Federation
 */
class Domain_Sso_Entity_Oidc_AccountData {

	/**
	 * получаем объект с основными данными об аккаунте SSO
	 *
	 * @param Struct_Db_SsoData_SsoAccountOidcToken $account_oidc_token
	 *
	 * @return Struct_Sso_AccountData
	 * @throws ParseFatalException
	 */
	public static function get(Struct_Db_SsoData_SsoAccountOidcToken $account_oidc_token):Struct_Sso_AccountData {

		// получаем id_token
		$id_token = $account_oidc_token->data->id_token;

		// в первую очередь пытаемся получить данные из user_info_data
		$mapped_attribute_first_name   = Domain_Sso_Entity_Oidc_Config::getMappedAttributeName(Domain_Sso_Entity_Oidc_Config::MAPPED_ATTRIBUTE_FIRST_NAME);
		$first_name                    = $account_oidc_token->data->user_info_data[$mapped_attribute_first_name] ?? Domain_Sso_Entity_Oidc_Token::getIdTokenCustomFieldValue($id_token, $mapped_attribute_first_name);
		$mapped_attribute_last_name    = Domain_Sso_Entity_Oidc_Config::getMappedAttributeName(Domain_Sso_Entity_Oidc_Config::MAPPED_ATTRIBUTE_LAST_NAME);
		$last_name                     = $account_oidc_token->data->user_info_data[$mapped_attribute_last_name] ?? Domain_Sso_Entity_Oidc_Token::getIdTokenCustomFieldValue($id_token, $mapped_attribute_last_name);
		$mapped_attribute_mail         = Domain_Sso_Entity_Oidc_Config::getMappedAttributeName(Domain_Sso_Entity_Oidc_Config::MAPPED_ATTRIBUTE_MAIL);
		$mail                          = $account_oidc_token->data->user_info_data[$mapped_attribute_mail] ?? Domain_Sso_Entity_Oidc_Token::getIdTokenCustomFieldValue($id_token, $mapped_attribute_mail);
		$mapped_attribute_phone_number = Domain_Sso_Entity_Oidc_Config::getMappedAttributeName(Domain_Sso_Entity_Oidc_Config::MAPPED_ATTRIBUTE_PHONE_NUMBER);
		$phone_number                  = $account_oidc_token->data->user_info_data[$mapped_attribute_phone_number] ?? Domain_Sso_Entity_Oidc_Token::getIdTokenCustomFieldValue($id_token, $mapped_attribute_phone_number);

		return new Struct_Sso_AccountData(
			first_name: $first_name,
			last_name: $last_name,
			mail: $mail,
			phone_number: $phone_number,
		);
	}
}