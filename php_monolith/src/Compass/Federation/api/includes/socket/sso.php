<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер для работы с SSO
 */
class Socket_Sso extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"validateAuthToken",
		"createUserRelationship",
	];

	/**
	 * метод для валидации токена попытки аутентификации через SSO
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function validateAuthToken():array {

		$sso_auth_token = $this->post(\Formatter::TYPE_STRING, "sso_auth_token");
		$signature      = $this->post(\Formatter::TYPE_STRING, "signature");

		try {

			/** @var Struct_Sso_AccountData $sso_account_data */
			[$compass_user_id, $sso_account_data] = Domain_Sso_Scenario_Socket_Auth::validateAuthToken($sso_auth_token, $signature);
		} catch (Domain_Sso_Exception_Auth_Expired) {
			return $this->error(1000, "sso auth expired");
		} catch (Domain_Sso_Exception_Auth_SignatureMismatch) {
			return $this->error(1001, "signature mismatch");
		} catch (Domain_Sso_Exception_Auth_TokenNotFound) {
			throw new ParseFatalException("unexpected behaviour");
		} catch (Domain_Sso_Exception_Auth_UnexpectedStatus) {
			return $this->error(1002, "auth have unexpected status");
		}

		return $this->ok([
			"compass_user_id"  => (int) $compass_user_id,
			"sso_account_data" => (array) $sso_account_data->format(),
		]);
	}

	/**
	 * создаем связь «SSO аккаунт» – «Пользователь Compass»
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws ParamException
	 * @throws \queryException
	 */
	public function createUserRelationship():array {

		$sso_auth_token = $this->post(\Formatter::TYPE_STRING, "sso_auth_token");
		$user_id        = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			Domain_Sso_Scenario_Socket_Auth::createUserRelationship($sso_auth_token, $user_id);
		} catch (Domain_Sso_Exception_Auth_TokenNotFound) {
			throw new ParseFatalException("unexpected behaviour");
		} catch (Domain_Sso_Exception_UserRelationship_AlreadyExists) {
			return $this->error(1003, "user relationship already exists");
		}

		return $this->ok();
	}
}