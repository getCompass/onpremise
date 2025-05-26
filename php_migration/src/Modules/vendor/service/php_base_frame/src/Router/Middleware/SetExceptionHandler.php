<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\BaseExceptionHandler;
use BaseFrame\Exception\ExceptionHandler;
use BaseFrame\Router\Request;

/**
 * Устанавливаем хендлер исключений в зависимости от версии точки входа
 */
class SetExceptionHandler implements Main{

	/**
	 * Устанавливает кастомный обработчик исключений.
	 */
	public static function handle(Request $request):Request {

		if (strtolower($request->extra["api_type"]) !== "apiv1") {
			ExceptionHandler::register(static fn(\Throwable $ex) => BaseExceptionHandler::instance()->work($ex));
		}

		return $request;
	}
}