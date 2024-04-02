<?php

namespace Compass\Federation;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * класс-обработчик для эндпоинта /sso/auth_result/oidc/ – сюда прилетают результаты
 * аутентификации по протоколу OpenID Connect
 */
class Domain_Sso_Scenario_Endpoint_Oidc {

	/**
	 * Обрабатываем получение ошибки
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public static function onError(string $error, string $error_description):void {

		$exception_message = $error;
		if (mb_strlen($error_description) > 0) {
			$exception_message .= " – " . $error_description;
		}

		throw new \BaseFrame\Exception\Request\ParamException($exception_message);
	}

	/**
	 * Обрабатываем получение кода авторизации
	 */
	public static function onReceiveAuthorizationCode(string $code, string $received_state):string {

		// распаковываем state
		try {
			$state = new Domain_Sso_Entity_State($received_state);
		} catch (Domain_Sso_Exception_Auth_State_Invalid) {
			throw new EndpointAccessDeniedException("unexpected behaviour");
		}

		// в $state должен вернуться токен аутентификации
		// пытаемся с его помощи получить попытку из базы
		try {
			$sso_auth = Domain_Sso_Entity_Auth::get($state->getSsoAuthToken());
		} catch (Domain_Sso_Exception_Auth_TokenNotFound) {
			throw new EndpointAccessDeniedException("unexpected behaviour");
		}

		// если попытка уже протухла
		if (Domain_Sso_Entity_Auth::isExpired($sso_auth)) {
			throw new EndpointAccessDeniedException("auth attempt is expired");
		}

		// если попытка уже завершена
		if (Domain_Sso_Entity_Auth::isSsoAuthComplete($sso_auth) || Domain_Sso_Entity_Auth::isCompassAuthComplete($sso_auth)) {
			throw new EndpointAccessDeniedException("auth attempt is complete");
		}

		// запрашиваем токены и сохраняем их
		Domain_Sso_Action_Auth_Oidc_RequestTokens::do($sso_auth, $code, $received_state);

		return $state->getRedirectUrl();
	}
}