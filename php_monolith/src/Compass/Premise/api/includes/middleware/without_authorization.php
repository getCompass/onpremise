<?php

namespace Compass\Premise;

use BaseFrame\Router\Request;

/**
 * Middleware авторизации запроса без пользователя.
 */
class Middleware_WithoutAuthorization implements \BaseFrame\Router\Middleware\Main {

	/**
	 * Функция — обработчик запроса.
	 * Должна обрабатывать входные данные для обработчика запросов.
	 *
	 * Возвращает массив, который затем будет закодирован в json для возврата.
	 */
	public static function handle(Request $request):Request {

		$request->user_id                       = $request->extra["need_user_id"];
		$request->extra["user"]["session_uniq"] = "";
		return $request;
	}
}
