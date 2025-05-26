<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Действие получения флага является ли сессия с недавним логином
 */
class Domain_User_Action_Security_Device_IsRecentlyLoginSession {

	/**
	 * Выполняем
	 */
	public static function do(int $login_at):bool {

		if (!IS_RECENTLY_LOGIN_SESSION_ENABLED) {
			return false;
		}

		// для тестового сервера по умолчанию считаем, что сессия не более ранняя
		if (ServerProvider::isTest() && getHeader("HTTP_TEST_RECENTLY_LOGIN_AT") != "") {
			$login_at = (int) getHeader("HTTP_TEST_RECENTLY_LOGIN_AT");
		}

		return $login_at > time() - Domain_User_Entity_SessionExtra::LOGIN_SESSION_TIMEOUT_FOR_INVALIDATION;
	}
}