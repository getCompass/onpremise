<?php

namespace Compass\Jitsi;

use BaseFrame\Router\Request;

/**
 * Проверяем у запроса наличие cookie guest_id и при отсутствии добавляем его
 */
class Middleware_WwwCookieGuestId {

	public static function handle(Request $request):Request {

		// если куки не установлены, то устанавливае их
		if (!Type_Session_GuestId::isSetup()) {
			Type_Session_GuestId::setup();
		}

		return $request;
	}
}