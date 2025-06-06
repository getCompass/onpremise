<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывает сценарий socket-методов аутентификации через SSO
 * @package Compass\Federation
 */
class Domain_Oidc_Scenario_Socket_Auth {

	/**
	 * валидируем токен попытки аутентификации
	 * проверяем, что попытка прошла успешную аутентификацию
	 *
	 * @return array
	 * @throws Domain_Oidc_Exception_Auth_Expired
	 * @throws Domain_Oidc_Exception_Auth_SignatureMismatch
	 * @throws Domain_Oidc_Exception_Auth_TokenNotFound
	 * @throws Domain_Oidc_Exception_Auth_UnexpectedStatus
	 * @throws ParseFatalException
	 */
	public static function validateAuthToken(string $sso_auth_token, string $signature):array {

		// получаем запись попытки аутентификации
		$sso_auth = Domain_Oidc_Entity_Auth::get($sso_auth_token);

		// проверяем, что прислали соответствующую подпись
		Domain_Oidc_Entity_Auth::assertSignature($sso_auth, $signature);

		// проверяем, что попытка не протухла
		Domain_Oidc_Entity_Auth::assertNotExpired($sso_auth);

		// проверяем, что попытка имеет соответствующий действию статус
		Domain_Oidc_Entity_Auth::assertStatus($sso_auth, Domain_Oidc_Entity_Auth::STATUS_SSO_AUTH_COMPLETE);

		// обновляем статус попытки
		Domain_Oidc_Entity_Auth::onCompassAuthComplete($sso_auth);

		// получаем запись с токенами попытки аутентификации
		$sso_account_oidc_token = Domain_Oidc_Entity_Protocol_Token::getByAuthToken($sso_auth_token);

		// достаем из ID токена всю информацию об sso аккаунте
		$sso_account_data = Domain_Oidc_Entity_Protocol_AccountData::get($sso_account_oidc_token);

		// получаем связь sso-аккунта и compass пользователя
		$compass_user_id = Domain_Oidc_Entity_AccountUserRel::getUserID($sso_account_oidc_token->sub_hash);

		return [$compass_user_id, $sso_account_data];
	}

	/**
	 * создаем связь «SSO аккаунт» – «Пользователь Compass»
	 *
	 * @param string $sso_auth_token
	 * @param int    $user_id
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 * @throws Domain_Oidc_Exception_Auth_TokenNotFound
	 * @throws Domain_Oidc_Exception_UserRelationship_AlreadyExists
	 */
	public static function createUserRelationship(string $sso_auth_token, int $user_id):void {

		// проверяем существование записи попытки аутентификации для переданного токена
		Domain_Oidc_Entity_Auth::get($sso_auth_token);

		// получаем запись с токенами попытки аутентификации
		$sso_account_oidc_token = Domain_Oidc_Entity_Protocol_Token::getByAuthToken($sso_auth_token);

		// создаем связь sso-аккаунта и compass пользователя
		Domain_Oidc_Entity_AccountUserRel::create(Domain_Oidc_Entity_Protocol_Token::getSub($sso_account_oidc_token->data->id_token), $user_id);
	}

	/**
	 * проверяем, что связь «SSO аккаунт» – «Пользователь Compass» существует
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function hasUserRelationship(int $user_id):bool {

		try {

			Domain_Oidc_Entity_AccountUserRel::getByUserID($user_id);
			return true;
		} catch (Domain_Oidc_Exception_UserRelationship_NotFound) {
		}

		return false;
	}

	/**
	 * удаляем связь «SSO аккаунт» – «Пользователь Compass», если она существует
	 *
	 * @param int $user_id
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	public static function deleteUserRelationship(int $user_id):void {

		Domain_Oidc_Entity_AccountUserRel::delete($user_id);
	}
}