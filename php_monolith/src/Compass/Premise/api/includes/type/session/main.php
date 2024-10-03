<?php

namespace Compass\Premise;

/**
 * класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _TOKEN_HEADER_UNIQUE = "pivot_authorization_token";                            // уникальный ключ установки заголовка
	protected const _PIVOT_COOKIE_KEY    = "pivot_session_key";                                    // ключ, передаваемый в cookie пользователя
	protected const _HEADER_AUTH_TYPE    = \BaseFrame\Http\Header\Authorization::AUTH_TYPE_BEARER; // тип токена для запроса

	protected const _SESSION_STATUS_GUEST      = 1;

	/**
	 * проверяем что сессия существует и валидна
	 * @return int
	 * @throws \cs_SessionNotFound
	 * @throws cs_CookieIsEmpty|\BaseFrame\Exception\Gateway\BusFatalException|\BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getUserIdBySession():int {

		try {
			$pivot_session_map = self::_getSessionMap();
		} catch (\BaseFrame\Exception\Request\EmptyAuthorizationException|\BaseFrame\Exception\Request\InvalidAuthorizationException) {
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
	 */
	public static function getSessionUniqBySession():string {

		try {
			$pivot_session_map = self::_getSessionMap();
		} catch (\BaseFrame\Exception\Request\EmptyAuthorizationException|\BaseFrame\Exception\Request\InvalidAuthorizationException) {
			throw new cs_CookieIsEmpty();
		}

		return Type_Pack_PivotSession::getSessionUniq($pivot_session_map);
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

			self::_clearRequestAuthData();
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


	/**
	 * Сбрасывает данные авторизации для клиента.
	 */
	protected static function _clearRequestAuthData():void {

		// готовим сброс заголовка
		static::_clearHeader();

		// сбрасываем авторизацию в куках,
		// даже если клиент пользовался заголовком
		static::_clearCookie();
	}

	/**
	 * Готовит данные для action сброса заголовка авторизации.
	 */
	protected static function _clearHeader():void {

		// инвалидируем заголовок
		\BaseFrame\Http\Header\Authorization::invalidate();

		// устанавливаем данные для authorization action
		\BaseFrame\Http\Authorization\Data::inst()->drop(static::_TOKEN_HEADER_UNIQUE);
	}

	/**
	 * Очищает cookie авторизации клиенту.
	 */
	protected static function _clearCookie():void {

		unset($_COOKIE[self::_PIVOT_COOKIE_KEY]);

		if (!isCLi()) {
			setcookie(self::_PIVOT_COOKIE_KEY, "", -1, "/", SESSION_COOKIE_DOMAIN);
		}
	}
}