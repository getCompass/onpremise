<?php

namespace Compass\Pivot;

use BaseFrame\Router\Request;

/**
 * Проверить доступность метода на онпремайз.
 */
class Middleware_OnPremiseCheckMethod implements \BaseFrame\Router\Middleware\Main {

	/**
	 * выполняем.
	 */
	public static function handle(Request $request):Request {

		if (isset($request->extra["allowed_methods_only_for_root"])
			&& in_array($request->route, $request->extra["allowed_methods_only_for_root"])
			&& $request->user_id !== Domain_User_Entity_OnpremiseRoot::getUserId()) {

			throw new \apiAccessException("method allowed only for root user on premise");
		}

		return $request;
	}
}