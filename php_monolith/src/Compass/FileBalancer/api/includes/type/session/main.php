<?php

namespace Compass\FileBalancer;

/**
 * класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _PIVOT_COOKIE_KEY    = "pivot_session_key";                                    // ключ, передаваемый в cookie пользователя
	protected const _HEADER_AUTH_TYPE    = \BaseFrame\Http\Header\Authorization::AUTH_TYPE_BEARER; // тип токена для запроса

	protected const _SESSION_STATUS_GUEST = 1;

	/**
	 * проверяем что сессия существует и валидна
	 * @throws \cs_SessionNotFound
	 * @throws cs_CookieIsEmpty
	 */
	public static function getSessionForPivot():array {

		// проверяем, что у пользователя установлена кука
		$pivot_session_map = self::_getSessionMap();
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
	 * Пытается получить session_map из данных запроса.
	 */
	protected static function _getSessionMap():string|false {

		// получаем наличие заголовка и ключ из заголовка
		// далее возможно процесс установки заголовка значением из кук
		[$has_header, $pivot_session_key] = static::_tryGetSessionMapFromAuthorizationHeader();

		// если в заголовке нет, то пытается достать из кук
		if ($has_header === false || $pivot_session_key === "") {
			$pivot_session_key = static::_tryGetSessionMapFromCookie();
		}

		// в куках тоже ничего не нашли
		if ($pivot_session_key === false) {
			return false;
		}

		// проверяем, что session_key валиден
		try {
			$pivot_session_map = Type_Pack_PivotSession::doDecrypt($pivot_session_key);
		} catch (\cs_DecryptHasFailed) {
			return false;
		}

		return $pivot_session_map;
	}

	/**
	 * Пытается получить токен из заголовка авторизации.
	 */
	protected static function _tryGetSessionMapFromAuthorizationHeader():array {

		// получаем заголовок авторизации
		$auth_header = \BaseFrame\Http\Header\Authorization::parse();

		// заголовка авторизации в запросе нет
		if ($auth_header === false) {
			return [false, ""];
		}

		// заголовок есть, но он пустой, т.е. клиент поддерживает
		// авторизацию через заголовок, но еще не получал токен
		if ($auth_header->isNone()) {
			return [true, ""];
		}

		// заголовок есть, но он имеет некорректный формат
		if (!$auth_header->isCorrect()) {
			return [false, ""];
		}

		// заголовок есть, он корректный, но предназначен не для этого
		// модуля/сервиса, считаем, что заголовка нет в таком случае
		if ($auth_header->getType() !== static::_HEADER_AUTH_TYPE) {
			return [false, ""];
		}

		return [true, base64_decode($auth_header->getToken())];
	}

	/**
	 * Пытается получить токен из cookie.
	 */
	protected static function _tryGetSessionMapFromCookie():string|false {

		// проверяем что сессии нет в куках
		if (!isset($_COOKIE[self::_PIVOT_COOKIE_KEY])) {
			return false;
		}

		return urldecode($_COOKIE[self::_PIVOT_COOKIE_KEY]);
	}
}