<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с токенами, полученными в результате успешной аутентификации через протокол OpenID Connect
 * @package Compass\Federation
 */
class Domain_Oidc_Entity_Protocol_Token {

	/**
	 * сохраняем токен
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function save(Struct_Db_SsoData_SsoAuth $sso_auth, Struct_Oidc_TokenResponse $token_response):void {

		Gateway_Db_SsoData_SsoAccountOidcTokenList::insert(new Struct_Db_SsoData_SsoAccountOidcToken(
			row_id: null,
			sub_hash: self::getSubHash(self::getSub($token_response->id_token)),
			sso_auth_token: $sso_auth->sso_auth_token,
			expires_at: time() + $token_response->expires_in,
			last_refresh_at: 0,
			created_at: time(),
			updated_at: 0,
			data: $token_response,
		));
	}

	/**
	 * получаем значение кастомного поля из id_token
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function getIdTokenCustomFieldValue(string $id_token, string $field_name):string {

		$payload_map = self::_getTokenPayloadMap($id_token);
		console($payload_map);

		if (!isset($payload_map[$field_name])) {

			Domain_Oidc_Entity_Logger::log("id_token не содержит ожидаемое поле $field_name");
			return "";
		}

		return $payload_map[$field_name];
	}

	/**
	 * получаем значение параметра sub из id_token
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function getSub(string $id_token):string {

		$payload_map = self::_getTokenPayloadMap($id_token);

		if (!isset($payload_map["sub"])) {

			Domain_Oidc_Entity_Logger::log("id_token payload не содержит поле sub");
			throw new ParseFatalException("unexpected id_token structure");
		}

		return $payload_map["sub"];
	}

	/**
	 * получаем sha1 хэш-сумму от значения параметра sub
	 *
	 * @param string $sub
	 *
	 * @return string
	 */
	public static function getSubHash(string $sub):string {

		return sha1($sub);
	}

	/**
	 * получаем ассоц. массив (словарь) payload из токена
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _getTokenPayloadMap(string $token):array {

		$temp = explode(".", $token);
		if (count($temp) !== 3) {
			throw new ParseFatalException("unexpected token");
		}

		return fromJson(Gateway_Sso_Oidc_Client::base64urlDecode($temp[1]));
	}

	/**
	 * получаем токен попытки аутентификации
	 *
	 * @return Struct_Db_SsoData_SsoAccountOidcToken
	 * @throws Domain_Oidc_Exception_Auth_TokenNotFound
	 * @throws ParseFatalException
	 */
	public static function getByAuthToken(string $sso_auth_token):Struct_Db_SsoData_SsoAccountOidcToken {

		try {
			return Gateway_Db_SsoData_SsoAccountOidcTokenList::getByAuthToken($sso_auth_token);
		} catch (RowNotFoundException) {
			throw new Domain_Oidc_Exception_Auth_TokenNotFound();
		}
	}
}