<?php

namespace Compass\Jitsi;

use BaseFrame\Router\Request;

/**
 *
 */
class Middleware_WithoutAuthorization implements \BaseFrame\Router\Middleware\Main {

	/**
	 * Функция — обработчик запроса.
	 * Должна обрабатывать входные данные для обработчика запросов.
	 *
	 * Возвращает массив, который затем будет закодирован в json для возврата.
	 */
	public static function handle(Request $request):Request {

		$request->user_id                       = 0;
		$request->extra["user"]["session_uniq"] = "";
		return $request;
	}
}