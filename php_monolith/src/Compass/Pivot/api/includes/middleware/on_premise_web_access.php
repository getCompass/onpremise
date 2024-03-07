<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Router\Request;
use BaseFrame\Server\ServerProvider;

/**
 * Доступ к on-premise web api-хендлеру
 */
class Middleware_OnPremiseWebAccess implements \BaseFrame\Router\Middleware\Main {

	/**
	 * авторизуем пользователя
	 * @long try..catch
	 */
	public static function handle(Request $request):Request {

		if (!ServerProvider::isOnPremise()) {
			throw new EndpointAccessDeniedException("Handler is not allowed on this environment");
		}

		return $request;
	}
}