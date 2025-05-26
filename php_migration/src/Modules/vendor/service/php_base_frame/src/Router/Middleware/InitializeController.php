<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Router\Request;

/**
 * Инициализировать контроллер
 */
class InitializeController implements Main {

	/**
	 * Инициализируем класс контроллера
	 */
	public static function handle(Request $request):Request {

		$request->controller_class = new $request->controller_name();
		return $request;
	}
}