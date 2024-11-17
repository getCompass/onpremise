<?php

namespace Compass\Jitsi;

/**
 * класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _PIVOT_COOKIE_KEY = "pivot_session_key";                                    // ключ, передаваемый в cookie пользователя
	protected const _HEADER_AUTH_TYPE = \BaseFrame\Http\Header\Authorization::AUTH_TYPE_BEARER; // тип токена для запроса

	protected const _SESSION_STATUS_GUEST = 1;

	/**
	 * проверяем что сессия существует и валидна
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\InvalidAuthorizationException
	 * @throws \BaseFrame\Exception\Request\EmptyAuthorizationException
	 */
	public static function getUserIdBySession():int {

		$pivot_session_map = self::_getSessionMap();

		if (Type_Pack_PivotSession::getStatus($pivot_session_map) == self::_SESSION_STATUS_GUEST) {
			return 0;
		}

		try {

			// получаем сессию из микросервиса авторизации
			$session = Gateway_Bus_PivotCache::getInfo(
				Type_Pack_PivotSession::getSessionUniq($pivot_session_map),
				Type_Pack_PivotSession::getShardId($pivot_session_map),
				Type_Pack_PivotSession::getTableId($pivot_session_map)
			);
		} catch (\cs_SessionNotFound $e) {
			throw new \BaseFrame\Exception\Request\InvalidAuthorizationException($e->getMessage());
		}

		return $session["user_id"];
	}

	/**
	 * Получаем session_uniq из сессии
	 * @throws \BaseFrame\Exception\Request\InvalidAuthorizationException
	 * @throws \BaseFrame\Exception\Request\EmptyAuthorizationException
	 */
	public static function getSessionUniqBySession():string {

		return Type_Pack_PivotSession::getSessionUniq(static::_getSessionMap());
	}

	/**
	 * Пытается получить session_map из данных запроса.
	 *
	 * @throws \BaseFrame\Exception\Request\EmptyAuthorizationException
	 * @throws \BaseFrame\Exception\Request\InvalidAuthorizationException
	 */
	protected static function _getSessionMap():string {

		$pivot_session_key = false;

		// получаем наличие заголовка и ключ из заголовка
		// далее возможно процесс установки заголовка значением из кук
		[$has_header, $header_pivot_session_token] = static::tryGetSessionMapFromAuthorizationHeader();

		// если в заголовке нет или токен заголовка
		// пустой, то достаем сессию из куки
		if ($has_header === false || $header_pivot_session_token === "") {
			$pivot_session_key = static::tryGetSessionMapFromCookie();
		}

		// если есть токен из заголовка, но нет ключа из кук, то
		// используем токен из заголовка как значение ключа
		if ($pivot_session_key === false && $header_pivot_session_token !== "") {
			$pivot_session_key = $header_pivot_session_token;
		}

		// если ничего не нашли, то сдаемся и считаем, что
		// клиент е прислал никаких данных авторизации запроса
		if ($pivot_session_key === false) {
			throw new \BaseFrame\Exception\Request\EmptyAuthorizationException("auth data not found");
		}

		// если ключ пустой, то и смысла его пытаться декодировать нет,
		// такое будет, если к нам пришел None заголовок и кука пустая
		if ($pivot_session_key === "") {
			throw new \BaseFrame\Exception\Request\EmptyAuthorizationException("auth data empty");
		}

		// проверяем, что session_key валиден
		try {
			$pivot_session_map = Type_Pack_PivotSession::doDecrypt($pivot_session_key);
		} catch (\cs_DecryptHasFailed) {
			throw new \BaseFrame\Exception\Request\InvalidAuthorizationException("decrypt failed");
		}

		return $pivot_session_map;
	}

	/**
	 * Пытается получить токен из заголовка авторизации.
	 */
	public static function tryGetSessionMapFromAuthorizationHeader():array {

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
	public static function tryGetSessionMapFromCookie():string|false {

		// проверяем что сессии нет в куках
		if (!isset($_COOKIE[self::_PIVOT_COOKIE_KEY])) {
			return false;
		}

		return urldecode($_COOKIE[self::_PIVOT_COOKIE_KEY]);
	}
}