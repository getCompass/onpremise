<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Router\Request;

/**
 *
 */
class CheckAllowedMethod implements Main {

	/**
	 * проверяем что вызываемый метод разрешен
	 */
	public static function handle(Request $request):Request {

		if (isset($request->extra["not_allowed_method_list"]) && in_array($request->route, $request->extra["not_allowed_method_list"])) {
			throw new ControllerMethodNotFoundException("METHOD in controller is not available");
		}

		return $request;
	}
}