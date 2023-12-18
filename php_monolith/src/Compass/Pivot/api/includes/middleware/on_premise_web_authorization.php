<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Router\Request;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Авторизация для веб-сайта on-premise решений.
 */
class Middleware_OnPremiseWebAuthorization implements \BaseFrame\Router\Middleware\Main {

	/**
	 * авторизуем пользователя
	 * @long try..catch
	 */
	public static function handle(Request $request):Request {

		try {

			$user_id      = Type_Session_Main::getUserIdBySession();
			$session_uniq = Type_Session_Main::getSessionUniqBySession();

			// если user_id не пустой, проверяем пользователя
			if ($user_id != 0) {

				// если пользователь удалил аккаунт, разлогиниваем пользователя
				$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
				if (Type_User_Main::isDisabledProfile($user_info->extra)) {

					Type_Session_Main::doLogoutSession($user_id);
					throw new \cs_SessionNotFound("session not found case profile deleted");
				}

				// проверяем, что к методу можно получить доступ с пустым профилем
				if (isset($request->extra["allowed_with_empty_profile"])
					&& !in_array($request->route, $request->extra["allowed_with_empty_profile"])
					&& Domain_User_Entity_User::isEmptyProfile($user_info)) {

					throw new cs_AnswerCommand("need_fill_profile", []);
				}
			}

		} catch (\cs_SessionNotFound|cs_CookieIsEmpty|ReturnFatalException) {

			$user_id = 0;

			$session      = Type_Session_Main::startSession();
			$session_uniq = Type_Pack_PivotSession::getSessionUniq($session);
		}

		if ($user_id === 0 && !in_array($request->controller_name, $request->extra["allowed_not_authorized"])) {
			throw new EndpointAccessDeniedException("User not authorized for this actions.");
		}

		$request->user_id                       = $user_id;
		$request->extra["user"]["session_uniq"] = $session_uniq;

		return $request;
	}
}