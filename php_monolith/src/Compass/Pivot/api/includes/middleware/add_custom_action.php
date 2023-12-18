<?php

namespace Compass\Pivot;

use BaseFrame\Router\Request;

/**
 * Добавляем action class
 */
class Middleware_AddCustomAction {

	/**
	 * инициализируем action класс
	 */
	public static function handle(Request $request):Request {

		$request->extra["action"] = Type_Api_Action::class;

		return $request;
	}
}