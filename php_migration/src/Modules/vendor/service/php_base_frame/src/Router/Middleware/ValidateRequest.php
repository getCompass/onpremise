<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Router\Request;

/**
 *
 */
class ValidateRequest implements Main {

	/**
	 * валидируем запрос
	 */
	public static function handle(Request $request):Request {

		$method = explode(".", strtolower($request->route));

		// если количество аргументов пришло неверное то выбрасываем что такого контроллера нет
		if (count($method) < 2 || count($method) > 3) {
			throw new ControllerMethodNotFoundException("CONTROLLER is not found.");
		}

		$controller  = $method[0];
		$method_name = $method[1];

		// для поддержки 3 уровня
		if (count($method) === 3) {

			$controller  .= "_" . $method[1];
			$method_name = $method[2];
		}

		// проверяем что задан метод внутри контролера
		if (mb_strlen($method_name) < 1) {
			throw new ControllerMethodNotFoundException("CONTROLLER method name is EMPTY. Check method action name.");
		}

		$request->method_name     = $method_name;
		$request->controller_name = $controller;

		return $request;
	}
}