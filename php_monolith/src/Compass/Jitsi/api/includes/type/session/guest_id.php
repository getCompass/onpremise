<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с кукой guest_id, используемой в www-недлере в функционале конференций jitsi
 * @package Compass\Jitsi
 */
class Type_Session_GuestId {

	/** @var string ключ куки */
	protected const _COOKIE_KEY = "guest_id";

	/** @var int длительность жизни куки */
	protected const _COOKIE_LIFE_TIME = 365 * DAY1;

	/**
	 * получаем куки
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function getGuestId():string {

		// установлены ли куки
		if (!self::isSetup()) {
			throw new ParseFatalException("cookie is not setup");
		}

		return $_COOKIE[self::_COOKIE_KEY];
	}

	/**
	 * проверяем, установлены ли куки
	 *
	 * @return bool
	 */
	public static function isSetup():bool {

		return isset($_COOKIE[self::_COOKIE_KEY]);
	}

	/**
	 * устанавливаем куки
	 */
	public static function setup(string $guest_id = null):void {

		if (is_null($guest_id)) {
			$guest_id = generateUUID();
		}

		$_COOKIE[self::_COOKIE_KEY] = $guest_id;
		setcookie(self::_COOKIE_KEY, $guest_id, time() + self::_COOKIE_LIFE_TIME, "/", SESSION_WEB_COOKIE_DOMAIN);
	}
}