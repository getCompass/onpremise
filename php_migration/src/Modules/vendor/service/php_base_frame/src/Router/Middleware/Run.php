<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Router\Request;

/**
 *
 */
class Run implements Main {

	/**
	 * запускаем контроллер в работу
	 */
	public static function handle(Request $request):Request {

		$request->response = $request->controller_class->work(
			$request->method_name, $request->method_version, $request->post_data, $request->user_id, $request->extra);

		return $request;
	}
}