<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс содержит логику socket-методов аутентификации по протоколу LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Scenario_Socket {

	/**
	 * Валидируем токен аутентификации
	 *
	 * @return array
	 * @throws Domain_Ldap_Exception_Auth_TokenNotFound
	 * @throws Domain_Ldap_Exception_Auth_UnexpectedStatus
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function validateAuthToken(string $ldap_auth_token):array {

		// получаем запись попытки аутентификации
		$ldap_auth = Domain_Ldap_Entity_AuthToken::get($ldap_auth_token);

		// проверяем, что попытка имеет соответствующий действию статус
		Domain_Ldap_Entity_AuthToken::assertStatus($ldap_auth, Domain_Ldap_Entity_AuthToken::STATUS_LDAP_AUTH_COMPLETE);

		// обновляем статус попытки
		Domain_Ldap_Entity_AuthToken::onCompassAuthComplete($ldap_auth);

		// собираем объект с данными о учетной записе
		$entry             = Domain_Ldap_Entity_Utils::prepareEntry(Domain_Ldap_Entity_AuthToken_Data::getEntry($ldap_auth->data));
		$ldap_account_data = Domain_Ldap_Entity_AccountData::parse($entry, $ldap_auth->username);

		// получаем связь ldap учетной записи и compass пользователя
		try {

			$account_user_rel = Domain_Ldap_Entity_AccountUserRel::get($ldap_auth->uid);
			$compass_user_id  = $account_user_rel->user_id;

			// переактивируем связь, если ранее LDAP аккаунт был заблокирован
			Domain_Ldap_Action_ReactivateAccountRel::do($account_user_rel);
		} catch (Domain_Ldap_Exception_UserRelationship_NotFound) {
			$compass_user_id = 0;
		}

		return [$compass_user_id, $ldap_account_data];
	}

	/**
	 * создаем связь «LDAP аккаунт» – «Пользователь Compass»
	 *
	 * @throws Domain_Ldap_Exception_Auth_TokenNotFound
	 * @throws Domain_Oidc_Exception_UserRelationship_AlreadyExists
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function createUserRelationship(string $ldap_auth_token, int $user_id):void {

		// получаем запись попытки аутентификации
		$ldap_auth = Domain_Ldap_Entity_AuthToken::get($ldap_auth_token);

		// создаем связь sso-аккаунта и compass пользователя
		Domain_Ldap_Entity_AccountUserRel::create(
			$ldap_auth->uid, $user_id, $ldap_auth->username, $ldap_auth->dn
		);
	}

	/**
	 * проверяем, что связь «LDAP аккаунт» – «Пользователь Compass» существует
	 *
	 * @return bool
	 */
	public static function hasUserRelationship(int $user_id):bool {

		try {

			$account_user_rel = Domain_Ldap_Entity_AccountUserRel::getByUserID($user_id);
		} catch (Domain_Ldap_Exception_UserRelationship_NotFound) {
			return false;
		}

		return $account_user_rel->status === Domain_Ldap_Entity_AccountUserRel::STATUS_ACTIVE;
	}

	/**
	 * удаляем связь «LDAP аккаунт» – «Пользователь Compass»
	 *
	 * @param int $user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function deleteUserRelationship(int $user_id):void {

		Domain_Ldap_Entity_AccountUserRel::delete($user_id);
	}
}