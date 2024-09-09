<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для работы с модулем federation
 */
class Gateway_Socket_Federation extends Gateway_Socket_Default {

	/**
	 * Валидируем попытку аутентификации через SSO по протоколу oidc
	 *
	 * @return array
	 * @throws Domain_User_Exception_AuthStory_Expired
	 * @throws Domain_User_Exception_AuthStory_Sso_SignatureMismatch
	 * @throws Domain_User_Exception_AuthStory_Sso_UnexpectedBehaviour
	 * @throws ReturnFatalException
	 */
	public static function validateSsoOidcAuthToken(string $sso_auth_token, string $signature):array {

		$ar_post = [
			"sso_auth_token" => $sso_auth_token,
			"signature"      => $signature,
		];
		[$status, $response] = self::_doCallSocket("oidc.validateAuthToken", $ar_post);

		if ($status === "error") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			match ($response["error_code"]) {
				1000 => throw new Domain_User_Exception_AuthStory_Expired(),
				1001 => throw new Domain_User_Exception_AuthStory_Sso_SignatureMismatch(),
				1002 => throw new Domain_User_Exception_AuthStory_Sso_UnexpectedBehaviour(),
			};
		}

		return [$response["compass_user_id"], Struct_User_Auth_Sso_AccountData::arrayToStruct($response["sso_account_data"])];
	}

	/**
	 * имеется ли связь пользователя с sso аккаунтом по протоколу oidc
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function hasSsoOidcUserRelationship(int $user_id):bool {

		$ar_post = [
			"user_id" => $user_id,
		];
		[$status, $response] = self::_doCallSocket("oidc.hasUserRelationship", $ar_post);

		if ($status === "error") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("unexpected behaviour");
		}

		return boolval($response["has_user_relationship"]);
	}

	/**
	 * оповещаем модуль федерации, чтобы сообщить о создании пользователя для успешной аутентификации SSO по протоколу OIDC
	 *
	 * @throws Domain_User_Exception_AuthStory_Sso_UserRelationshipAlreadyExists
	 * @throws ReturnFatalException
	 */
	public static function createSsoOidcUserRelationship(string $sso_auth_token, int $user_id):void {

		$ar_post = [
			"sso_auth_token" => $sso_auth_token,
			"user_id"        => $user_id,
		];
		[$status, $response] = self::_doCallSocket("oidc.createUserRelationship", $ar_post);

		if ($status === "error") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			match ($response["error_code"]) {
				1003 => throw new Domain_User_Exception_AuthStory_Sso_UserRelationshipAlreadyExists(),
			};
		}
	}

	/**
	 * Валидируем попытку аутентификации через SSO по протоколу LDAP
	 *
	 * @return array
	 * @throws Domain_User_Exception_AuthStory_Ldap_UnexpectedBehaviour
	 * @throws ReturnFatalException
	 */
	public static function validateSsoLdapAuthToken(string $ldap_auth_token):array {

		$ar_post = [
			"ldap_auth_token" => $ldap_auth_token,
		];
		[$status, $response] = self::_doCallSocket("ldap.validateAuthToken", $ar_post);

		if ($status === "error") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			match ($response["error_code"]) {
				// если токен был использован или не найден
				1002, 1004 => throw new Domain_User_Exception_AuthStory_Ldap_UnexpectedBehaviour(),
			};
		}

		return [$response["compass_user_id"], Struct_User_Auth_Ldap_AccountData::arrayToStruct($response["ldap_account_data"])];
	}

	/**
	 * оповещаем модуль федерации, чтобы сообщить о создании пользователя для успешной аутентификации SSO по протоколу LDAP
	 *
	 * @throws Domain_User_Exception_AuthStory_Ldap_UserRelationship_AlreadyExists
	 * @throws ReturnFatalException
	 */
	public static function createSsoLdapUserRelationship(string $ldap_auth_token, int $user_id):void {

		$ar_post = [
			"ldap_auth_token" => $ldap_auth_token,
			"user_id"         => $user_id,
		];
		[$status, $response] = self::_doCallSocket("ldap.createUserRelationship", $ar_post);

		if ($status === "error") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			match ($response["error_code"]) {
				1003 => throw new Domain_User_Exception_AuthStory_Ldap_UserRelationship_AlreadyExists(),
			};
		}
	}

	/**
	 * имеется ли связь пользователя с sso аккаунтом по протоколу ldap
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function hasSsoLdapUserRelationship(int $user_id):bool {

		$ar_post = [
			"user_id" => $user_id,
		];
		[$status, $response] = self::_doCallSocket("ldap.hasUserRelationship", $ar_post);

		if ($status === "error") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("unexpected behaviour");
		}

		return boolval($response["has_user_relationship"]);
	}

	/**
	 * имеется ли связь пользователя с sso аккаунтом любого протокола (oidc, ldap, ...)
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function hasSsoUserRelationship(int $user_id):bool {

		$ar_post = [
			"user_id" => $user_id,
		];
		[$status, $response] = self::_doCallSocket("sso.hasUserRelationship", $ar_post);

		if ($status === "error" || !isset($response["has_user_relationship"])) {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("unexpected behaviour");
		}

		return boolval($response["has_user_relationship"]);
	}

	/**
	 * удаляем связь пользователя с sso аккунтом любого протокола (oidc, ldap, ...)
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function deleteSsoUserRelationship(int $user_id):void {

		$ar_post = [
			"user_id" => $user_id,
		];
		[$status, $response] = self::_doCallSocket("sso.deleteUserRelationship", $ar_post);

		if ($status === "error") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("unexpected behaviour");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// выполняем socket запрос
	protected static function _doCallSocket(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketFederationUrl();
		return self::_doCall($url, $method, $params, $user_id);
	}
}
