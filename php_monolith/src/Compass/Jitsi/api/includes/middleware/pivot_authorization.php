<?php

namespace Compass\Jitsi;

use BaseFrame\Router\Request;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Авторизация
 */
class Middleware_PivotAuthorization implements \BaseFrame\Router\Middleware\Main {

	/**
	 * авторизуем пользователя
	 */
	public static function handle(Request $request):Request {

		try {

			$user_id      = Type_Session_Main::getUserIdBySession();
			$session_uniq = Type_Session_Main::getSessionUniqBySession();
		} catch (\cs_SessionNotFound|cs_CookieIsEmpty|ReturnFatalException) {

			// если не нашли сессию в базах то она скорее всего не активна больше
			// если куки пусты или сессия не валидна просим получить сессию
			throw new cs_AnswerCommand("need_call_start", []);
		}

		if ($user_id == 0) {

			if (!isset($request->extra["allowed_without_controller"]) || !in_array($request->controller_name, $request->extra["not_auth_controller_list"])) {
				throw new EndpointAccessDeniedException("User not authorized for this actions.");
			}
		}

		$request->user_id                       = $user_id;
		$request->extra["user"]["session_uniq"] = $session_uniq;

		return $request;
	}
}