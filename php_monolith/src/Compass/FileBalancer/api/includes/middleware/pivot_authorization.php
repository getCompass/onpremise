<?php

namespace Compass\FileBalancer;

use BaseFrame\Router\Request;

/**
 * Авторизация
 */
class Middleware_PivotAuthorization implements \BaseFrame\Router\Middleware\Main {

	/**
	 * авторизуем пользователя
	 */
	public static function handle(Request $request):Request {

		try {

			// проверяем сессию
			[$user_id, $session_uniq] = Type_Session_Main::getSessionForPivot();
		} catch (\cs_SessionNotFound | cs_CookieIsEmpty) {

			// если не нашли сессию в базах то она скорее всего не активна больше
			throw new \userAccessException("user_session not found");
		}

		if ($user_id == 0) {

			if (!isset($request->extra["allowed_without_controller"]) || !in_array($request->controller_name, $request->extra["not_auth_controller_list"])) {
				throw new \userAccessException("User not authorized for this actions.");
			}
		}
		$request->user_id                       = $user_id;
		$request->extra["user"]["session_uniq"] = $session_uniq;

		return $request;
	}
}