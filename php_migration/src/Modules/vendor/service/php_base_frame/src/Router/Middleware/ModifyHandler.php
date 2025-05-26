<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Router\Request;

/**
 *
 */
class ModifyHandler implements Main {

	/**
	 * модифицируем имя котроллера под апи запрос
	 */
	public static function handle(Request $request):Request {

		$request->controller_name = $request->extra["namespace"] . "\\" . $request->extra["api_type"] . "_{$request->controller_name}";
		return $request;
	}
}