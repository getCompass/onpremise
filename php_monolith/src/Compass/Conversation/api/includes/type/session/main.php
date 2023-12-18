<?php

namespace Compass\Conversation;

/**
 * класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _COMPANY_COOKIE_KEY = COMPANY_ID . "_company_session_key"; // ключ, передаваемый в cookie пользователя

	/**
	 * проверяем что сессия существует и валидна
	 *
	 * @throws \busException
	 * @throws cs_SessionNotFound
	 */
	public static function getSession():array {

		// проверяем, что у пользователя установлена кука
		$cloud_session_uniq = self::_getCloudSessionUniqFromCookie();
		if ($cloud_session_uniq === false) {
			return [0, false, false, false];
		}

		// отдаем сессию пользователя
		$member = Gateway_Bus_CompanyCache::getSessionInfo($cloud_session_uniq);
		return [$member->user_id, $cloud_session_uniq, $member->role, $member->permissions];
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	/**
	 * получаем session map из куки
	 *
	 * @return false|string
	 * @mixed
	 * @throws cs_SessionNotFound
	 */
	protected static function _getCloudSessionUniqFromCookie():bool | string {

		// проверяем что сессии нет в куках
		if (!isset($_COOKIE[self::_COMPANY_COOKIE_KEY])) {
			return false;
		}

		try {
			return \CompassApp\Pack\CompanySession::getSessionUniq(\CompassApp\Pack\CompanySession::doDecrypt(urldecode($_COOKIE[self::_COMPANY_COOKIE_KEY])));
		} catch (\cs_DecryptHasFailed) {
			throw new cs_SessionNotFound();
		}
	}
}