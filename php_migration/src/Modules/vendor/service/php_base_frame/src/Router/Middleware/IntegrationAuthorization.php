<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Router\Request;

/**
 * middleware для авторизации интеграций
 */
class IntegrationAuthorization implements Main {

	/**
	 * поверяем безопасность данных в запросе
	 */
	public static function handle(Request $request):Request {

		$authorization_token        = $request->extra["authorization_token"];
		$header_authorization_token = self::_getAuthorizationToken();

		$request->user_id = 0;

		// сверяем токен
		if ($authorization_token !== $header_authorization_token) {
			throw new EndpointAccessDeniedException("User not authorized for this actions.");
		}

		return $request;
	}

	/**
	 * получаем данные для авторизации запроса
	 */
	protected static function _getAuthorizationToken():string {

		// ожидаем заголовок формата "Authorization: bearer=<токен бота>"
		$header_for_token = getHeader("HTTP_AUTHORIZATION");
		$tmp              = explode("=", $header_for_token);
		if (count($tmp) != 2 || trim(mb_strtolower($tmp[0])) != "bearer") {
			throw new EndpointAccessDeniedException("User not authorized for this actions.");
		}
		return trim($tmp[1]);
	}
}