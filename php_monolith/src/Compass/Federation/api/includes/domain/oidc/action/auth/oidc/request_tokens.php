<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывает действие по запросу токенов
 * @package Compass\Federation
 */
class Domain_Oidc_Action_Auth_Oidc_RequestTokens {

	/**
	 * @param Struct_Db_SsoData_SsoAuth $sso_auth
	 * @param string                    $code
	 * @param string                    $state
	 *
	 * @throws Domain_Oidc_Exception_Auth_Protocol_RequestTokensFailed
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function do(Struct_Db_SsoData_SsoAuth $sso_auth, string $code, string $state):void {

		// создаем клиент для работы с SSO
		$client = Domain_Oidc_Entity_Protocol_Client::init();
		$client->setOriginalState(Domain_Oidc_Entity_State::parseStateFromAuthorizationLink($sso_auth->link));
		$client->setOriginalNonce(Domain_Oidc_Entity_Protocol_Client::getNonceByAuthStruct($sso_auth));

		// запрашиваем токены
		try {

			$result         = $client->authenticateByCode($code, $state);
			$token_response = Struct_Oidc_TokenResponse::arrayToStruct((array) $result);
		} catch (Gateway_Sso_Oidc_Exception $e) {

			Domain_Oidc_Entity_Logger::log($e->getMessage(), ["trace" => $e->getTrace()]);
			throw new Domain_Oidc_Exception_Auth_Protocol_RequestTokensFailed($e->getMessage());
		}

		try {

			// также пытаемся получить информацию об учетной записи
			$user_info_data = $client->requestUserInfo();
			$token_response->setUserInfoData($user_info_data);
		} catch (Gateway_Sso_Oidc_Exception) {

			// если не удалось (а это может быть с adfs решением, так как оно отключило поддержку user_ifno)
			// то не расстраиваемся
		}

		// обновляем попытку аутентификации
		Domain_Oidc_Entity_Auth::onSsoAuthComplete($sso_auth);

		// сохраняем токены
		Domain_Oidc_Entity_Protocol_Token::save($sso_auth, $token_response);
	}
}