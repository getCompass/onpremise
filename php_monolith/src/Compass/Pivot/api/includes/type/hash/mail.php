<?php

namespace Compass\Pivot;

/**
 * Класс для хэширования почты
 */
class Type_Hash_Mail extends Type_Hash_Main {

	/**
	 * Хэшировать почту
	 */
	public static function makeHash(string $mail):string {

		return self::_makeHash($mail, SALT_MAIL_ADDRESS);
	}

	/**
	 * Сравнить хэши
	 */
	public static function compareHash(string $hash, string $mail):bool {

		return self::_compareHash($hash, $mail, SALT_MAIL_ADDRESS);
	}
}