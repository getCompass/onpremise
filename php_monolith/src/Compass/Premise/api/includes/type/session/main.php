<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _PIVOT_COOKIE_KEY = "pivot_session_key"; // ключ, передаваемый в cookie пользователя

	protected const _SESSION_STATUS_GUEST = 1;

	/**
	 * проверяем что сессия существует и валидна
	 * @return int
	 * @throws \cs_SessionNotFound
	 * @throws \busException|cs_CookieIsEmpty|\returnException
	 */
	public static function getUserIdBySession():int {

		// проверяем, что у пользователя установлена кука
		$pivot_session_map = self::_getSessionMapFromCookie();

		if (mb_strlen($pivot_session_map) < 1) {
			throw new cs_CookieIsEmpty();
		}

		if (Type_Pack_PivotSession::getStatus($pivot_session_map) == self::_SESSION_STATUS_GUEST) {
			return 0;
		}

		// отдаем сессию пользователя
		$session = Gateway_Bus_PivotCache::getInfo(
			Type_Pack_PivotSession::getSessionUniq($pivot_session_map),
			Type_Pack_PivotSession::getShardId($pivot_session_map),
			Type_Pack_PivotSession::getTableId($pivot_session_map)
		);

		return $session["user_id"];
	}

	/**
	 * Получаем session_uniq из сессии
	 *
	 * @return string
	 * @throws cs_CookieIsEmpty
	 * @throws \returnException
	 */
	public static function getSessionUniqBySession():string {

		// проверяем, что у пользователя установлена кука
		$pivot_session_map = self::_getSessionMapFromCookie();
		if (mb_strlen($pivot_session_map) < 1) {
			throw new cs_CookieIsEmpty();
		}

		return Type_Pack_PivotSession::getSessionUniq($pivot_session_map);
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	/**
	 * получаем session map из куки
	 *
	 * @return string
	 * @throws \returnException
	 * @mixed
	 */
	protected static function _getSessionMapFromCookie():string {

		// проверяем что сессии нет в куках
		if (!isset($_COOKIE[self::_PIVOT_COOKIE_KEY])) {
			return "";
		}

		$pivot_session_key = urldecode($_COOKIE[self::_PIVOT_COOKIE_KEY]);

		// проверяем, что session_key валиден
		try {
			$pivot_session_map = Type_Pack_PivotSession::doDecrypt($pivot_session_key);
		} catch (\cs_DecryptHasFailed) {

			self::_clearCookie();
			throw new ReturnFatalException("decrypt failed");
		}
		return $pivot_session_map;
	}

	// функция для очистки куки
	protected static function _clearCookie():void {

		unset($_COOKIE[self::_PIVOT_COOKIE_KEY]);

		if (!isCLi()) {
			setcookie(self::_PIVOT_COOKIE_KEY, "", -1, "/", SESSION_COOKIE_DOMAIN);
		}
	}
}