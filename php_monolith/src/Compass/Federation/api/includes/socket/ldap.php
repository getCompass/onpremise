<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер для работы с SSO по протоколу LDAP
 */
class Socket_Ldap extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"validateAuthToken",
		"createUserRelationship",
		"getUserLdapData",
		"hasUserRelationship",
	];

	/**
	 * метод для валидации токена попытки аутентификации через SSO LDAP
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function validateAuthToken():array {

		$ldap_auth_token = $this->post(\Formatter::TYPE_STRING, "ldap_auth_token");

		try {

			/** @var Struct_Ldap_AccountData $ldap_account_data */
			[$compass_user_id, $ldap_account_data] = Domain_Ldap_Scenario_Socket::validateAuthToken($ldap_auth_token);
		} catch (Domain_Ldap_Exception_Auth_TokenNotFound) {
			return $this->error(1004, "token not found");
		} catch (Domain_Ldap_Exception_Auth_UnexpectedStatus) {
			return $this->error(1002, "auth have unexpected status");
		}

		return $this->ok([
			"compass_user_id"   => (int) $compass_user_id,
			"ldap_account_data" => (object) $ldap_account_data->format(),
		]);
	}

	/**
	 * создаем связь «LDAP аккаунт» – «Пользователь Compass»
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws ParamException
	 * @throws \queryException
	 */
	public function createUserRelationship():array {

		$ldap_auth_token = $this->post(\Formatter::TYPE_STRING, "ldap_auth_token");
		$user_id         = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			Domain_Ldap_Scenario_Socket::createUserRelationship($ldap_auth_token, $user_id);
		} catch (Domain_Ldap_Exception_Auth_TokenNotFound) {
			throw new ParseFatalException("unexpected behaviour");
		} catch (Domain_Ldap_Exception_UserRelationship_AlreadyExists) {
			return $this->error(1003, "user relationship already exists");
		}

		return $this->ok();
	}

	/**
	 * Получить данные ldap учётной записи для пользователя.
	 */
	public function getUserLdapData():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			$ldap_account_data = Domain_Ldap_Scenario_Socket::getUserLdapData($user_id);
		} catch (Domain_Ldap_Exception_Auth_TokenNotFound|Domain_Ldap_Exception_UserRelationship_NotFound) {
			return $this->error(1004, "token not found");
		}

		return $this->ok([
			"ldap_account_data" => (object) $ldap_account_data->format(),
		]);
	}

	/**
	 * проверяем, что связь «LDAP аккаунт» – «Пользователь Compass» существует
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function hasUserRelationship():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		$has_user_relationship = Domain_Ldap_Scenario_Socket::hasUserRelationship($user_id);

		return $this->ok([
			"has_user_relationship" => (int) intval($has_user_relationship),
		]);
	}
}