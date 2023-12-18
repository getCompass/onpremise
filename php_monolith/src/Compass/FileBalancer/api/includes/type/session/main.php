<?php

namespace Compass\FileBalancer;

/**
 * класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _COMPANY_COOKIE_KEY = COMPANY_ID . "_company_session_key"; // ключ, передаваемый в cookie пользователя
	protected const _PIVOT_COOKIE_KEY   = "pivot_session_key"; // ключ, передаваемый в cookie пользователя

	protected const _SESSION_STATUS_GUEST = 1;

	/**
	 * проверяем что сессия существует и валидна
	 *
	 * @throws busException
	 * @throws cs_SessionNotFound
	 */
	public static function getSessionForCompany():array {

		// проверяем, что у пользователя установлена кука
		$cloud_session_uniq = self::_getCloudSessionUniqFromCookie();
		if ($cloud_session_uniq === false) {
			return [0, false];
		}

		// отдаем сессию пользователя
		$user_id = Gateway_Bus_CompanyCache::getSessionInfo($cloud_session_uniq);
		return [$user_id, $cloud_session_uniq];
	}

	/**
	 * проверяем что сессия существует и валидна
	 *
	 * @throws busException
	 * @throws cs_CookieIsEmpty
	 * @throws cs_SessionNotFound
	 * @throws returnException
	 */
	public static function getSessionForPivot():array {

		// проверяем, что у пользователя установлена кука
		$pivot_session_map = self::_getSessionMapFromCookie();
		if ($pivot_session_map === false) {
			throw new cs_CookieIsEmpty();
		}

		if (Type_Pack_PivotSession::getStatus($pivot_session_map) == self::_SESSION_STATUS_GUEST) {
			return [0, ""];
		}

		// отдаем сессию пользователя
		$user_id = Gateway_Bus_PivotCache::getInfo(
			Type_Pack_PivotSession::getSessionUniq($pivot_session_map),
			Type_Pack_PivotSession::getShardId($pivot_session_map),
			Type_Pack_PivotSession::getTableId($pivot_session_map)
		);

		$pivot_session_uniq = Type_Pack_PivotSession::getSessionUniq($pivot_session_map);

		return [$user_id, $pivot_session_uniq];
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	/**
	 * получаем session map из куки
	 *
	 * @return false|string
	 * @mixed
	 */
	protected static function _getCloudSessionUniqFromCookie():bool|string {

		// проверяем что сессии нет в куках
		if (!isset($_COOKIE[self::_COMPANY_COOKIE_KEY])) {
			return false;
		}

		try {
			return Type_Pack_CompanySession::getSessionUniq(Type_Pack_CompanySession::doDecrypt(urldecode($_COOKIE[self::_COMPANY_COOKIE_KEY])));
		} catch (cs_DecryptHasFailed) {
			throw new cs_SessionNotFound();
		}
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	/**
	 * получаем session map из куки
	 *
	 * @return false|string
	 *
	 * @throws returnException
	 * @mixed
	 */
	protected static function _getSessionMapFromCookie() {

		// проверяем что сессии нет в куках
		if (!isset($_COOKIE[self::_PIVOT_COOKIE_KEY])) {
			return false;
		}

		$pivot_session_key = urldecode($_COOKIE[self::_PIVOT_COOKIE_KEY]);

		// проверяем, что session_key валиден
		try {
			$pivot_session_map = Type_Pack_PivotSession::doDecrypt($pivot_session_key);
		} catch (cs_DecryptHasFailed $_) {
			throw new returnException();
		}
		return $pivot_session_map;
	}
}