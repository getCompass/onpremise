<?php

namespace Compass\Jitsi;

use BaseFrame\Router\Request;

/**
 * Берет имеющуюся cookie авторизации и, при необходимости, делает из нее action authorization.
 */
class Middleware_AuthCookieToHeader implements \BaseFrame\Router\Middleware\Main {

	/**
	 * Берет имеющуюся cookie авторизации и, при необходимости, делает из нее action authorization.
	 * Нужно для бесшовной миграции авторизации из cookie в заголовки.
	 */
	public static function handle(Request $request):Request {

		[$has_header, $header_session_key] = Type_Session_Main::tryGetSessionMapFromAuthorizationHeader();

		// замену будем проводить только, если у нас есть заголовок и он не пустой
		if ($has_header !== false && $header_session_key === "") {

			// получаем ключ из cookie
			$cookie_session_key = Type_Session_Main::tryGetSessionMapFromCookie();

			// если в куках есть сессия, то подхватываем ее
			if ($cookie_session_key !== false) {
				Type_Session_Main::setHeaderAction($cookie_session_key);
			}
		}

		return $request;
	}
}