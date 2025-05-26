<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Router\Request;
use BaseFrame\Server\ServerProvider;

/**
 * middleware для проверки наличия интеграции на сервере
 */
class IntegrationEnabled implements Main {

	/**
	 * поверяем безопасность данных в запросе
	 */
	public static function handle(Request $request):Request {

		if (!ServerProvider::isIntegration()) {
			throw new EndpointAccessDeniedException("Integration is not enabled");
		}

		return $request;
	}
}