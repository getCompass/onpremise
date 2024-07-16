<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для работы с модулем federation
 */
class Gateway_Socket_Federation extends Gateway_Socket_Default {

	/**
	 * Валидируем попытку аутентификации
	 *
	 * @return array
	 * @throws Domain_User_Exception_AuthStory_Expired
	 * @throws Domain_User_Exception_AuthStory_Sso_SignatureMismatch
	 * @throws Domain_User_Exception_AuthStory_Sso_UnexpectedBehaviour
	 * @throws ReturnFatalException
	 */
	public static function validateSsoAuthToken(string $sso_auth_token, string $signature):array {

		$ar_post = [
			"sso_auth_token" => $sso_auth_token,
			"signature"      => $signature,
		];
		[$status, $response] = self::_doCallSocket("sso.validateAuthToken", $ar_post);

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
	 * имеется ли связь пользователя с sso аккаунтом
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function hasUserRelationship(int $user_id):bool {

		$ar_post = [
			"user_id"        => $user_id,
		];
		[$status, $response] = self::_doCallSocket("sso.hasUserRelationship", $ar_post);

		if ($status === "error") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("unexpected behaviour");
		}

		return boolval($response["has_user_relationship"]);
	}

	/**
	 * оповещаем модуль федерации, чтобы сообщить о создании пользователя для успешной аутентификации
	 *
	 * @throws Domain_User_Exception_AuthStory_Expired
	 * @throws ReturnFatalException
	 */
	public static function createUserRelationship(string $sso_auth_token, int $user_id):void {

		$ar_post = [
			"sso_auth_token" => $sso_auth_token,
			"user_id"        => $user_id,
		];
		[$status, $response] = self::_doCallSocket("sso.createUserRelationship", $ar_post);

		if ($status === "error") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			match ($response["error_code"]) {
				1003 => throw new Domain_User_Exception_AuthStory_Sso_UserRelationship_AlreadyExists(),
			};
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
