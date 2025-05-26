<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Router\Request;

/**
 * middleware для установки версии метода
 */
class SetMethodVersion implements Main {

	/**
	 * Устанавливаем версию метода
	 *
	 * @param Request $request
	 *
	 * @return Request
	 * @throws ParamException
	 */
	public static function handle(Request $request):Request {

		// достаем версию метода из хедера
		$method_version = getHeader("HTTP_X_COMPASS_METHOD_VERSION");

		// если в хедере не передано значение - просто идем дальше
		if (!$method_version) {
			return $request;
		}

		// пытаемся извлечь из параметра число и проверяем, что оно не меньше 1
		if (filter_var($method_version, FILTER_VALIDATE_INT) === false || $method_version < 1) {
			throw new ParamException("invalid method version in header");
		}

		// устанавливаем реквесту версию метода
		$request->method_version = $method_version;

		return $request;
	}
}