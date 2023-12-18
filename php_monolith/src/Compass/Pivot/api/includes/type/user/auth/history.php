<?php

namespace Compass\Pivot;

/**
 * Класс для основной работы по логированию авторизации пользователя
 */
class Type_User_Auth_History {

	protected const _EVENT_KEY = "user_auth_history";

	/**
	 * пользователь зашел
	 *
	 */
	public static function doStart(int $user_id):void {

		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, [
			"uniq_key" => self::makeHash($user_id),
		]);
	}

	/**
	 * Хэшировать $user_id, подойдет любая соль
	 *
	 */
	public static function makeHash(int $user_id):string {

		return self::_makeHash($user_id, SALT_PHONE_NUMBER);
	}

	/**
	 * Хэшируем значение
	 *
	 */
	protected static function _makeHash(mixed $value, string $salt):string {

		return hash_hmac("sha1", (string) $value, $salt);
	}
}