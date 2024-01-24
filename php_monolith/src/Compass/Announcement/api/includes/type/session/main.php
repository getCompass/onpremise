<?php

namespace Compass\Announcement;

/**
 * Класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _TOKEN_COOKIE_KEY = "authorization_token"; // ключ, передаваемый в cookie пользователя

	/**
	 * Получить токен из куки
	 *
	 * @return string
	 */
	public static function getTokenFromCookie():string {

		return $_COOKIE[self::_TOKEN_COOKIE_KEY] ?? "";
	}

	/**
	 * Сохранить токен в куки
	 *
	 * @param string $token
	 */
	public static function setTokenToCookie(string $token):void {

		// если работаем из консоли
		if (isCLi()) {
			return;
		}

		if (DOMAIN_ANNOUNCEMENT !== "") {

			// удаляем старую куку, у которой был пустой домен
			setcookie(self::_TOKEN_COOKIE_KEY, "", -1, "/", "");
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
	 * Удалить токен из куки
	 */
	public static function removeTokenFromCookie():void {

		unset($_COOKIE[self::_TOKEN_COOKIE_KEY]);

		if (!isCLi()) {
			setcookie(self::_TOKEN_COOKIE_KEY, "", -1, "/", DOMAIN_ANNOUNCEMENT);
		}
	}
}