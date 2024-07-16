<?php

namespace Compass\Announcement;

/**
 * Класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _TOKEN_HEADER_UNIQUE = "announcement_authorization_token";                     // ключ, передаваемый в заголовке пользователя
	protected const _TOKEN_COOKIE_KEY    = "authorization_token";                                  // ключ, передаваемый в cookie пользователя
	protected const _HEADER_AUTH_TYPE    = \BaseFrame\Http\Header\Authorization::AUTH_TYPE_BEARER; // тип токена для запроса

	/**
	 * Получить токен из куки
	 */
	public static function getAuthToken():string {

		// получаем наличие заголовка и ключ из заголовка
		// далее возможно процесс установки заголовка значением из кук
		[$has_header, $token] = static::tryGetSessionMapFromAuthorizationHeader();

		if ($has_header && $token !== "") {
			return $token;
		}

		return static::tryGetSessionMapFromCookie() ?: "";
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

		return $_COOKIE[self::_TOKEN_COOKIE_KEY] ?? "";
	}

	/**
	 * Устанавливает клиенту данные авторизации.
	 */
	public static function setAuthToken(string $token):void {

		static::setHeaderAction($token);
		static::_setCookie($token);
	}

	/**
	 * Устанавливает данные, которые клиент клиент будет использовать для авторизации в дальнейшем.
	 *
	 * <b>!!! Функция имеет public уровень только для миграции куки -> заголовок, в остальных
	 *  случая публичное использование функции запрещено !!!<b>
	 */
	public static function setHeaderAction(string $token):void {

		$auth_item = Type_Session_Main::makeAuthenticationItem($token);
		\BaseFrame\Http\Authorization\Data::inst()->set($auth_item["unique"], $auth_item["token"]);
	}

	/**
	 * Устанавливает cookie авторизации клиенту.
	 */
	protected static function _setCookie(string $token):void {

		// если работаем из консоли
		if (isCLi()) {
			return;
		}

		// устанавливаем session_key для пользователя
		setcookie(
			self::_TOKEN_COOKIE_KEY,
			urlencode($token),
			time() + DAY1 * 360,
			"/", DOMAIN_ANNOUNCEMENT,
			false,
			false
		);
	}

	/**
	 * Сбрасывает данные авторизации для клиента.
	 */
	public static function dropAuthToken():void {

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

		unset($_COOKIE[self::_TOKEN_COOKIE_KEY]);

		if (!isCLi()) {
			setcookie(self::_TOKEN_COOKIE_KEY, "", -1, "/", DOMAIN_ANNOUNCEMENT);
		}
	}

	/**
	 * Формирует объект с данными авторизации клиента.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["unique" => "string", "token" => "string"])]
	public static function makeAuthenticationItem(string $token):array {

		return [
			"unique" => static::_TOKEN_HEADER_UNIQUE,
			"token"  => sprintf("%s %s", static::_HEADER_AUTH_TYPE, base64_encode($token))
		];
	}
}