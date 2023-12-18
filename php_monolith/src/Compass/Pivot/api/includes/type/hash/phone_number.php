<?php

namespace Compass\Pivot;

/**
 * Класс для хэширования телефонов
 */
class Type_Hash_PhoneNumber extends Type_Hash_Main {

	/**
	 * Хэшировать номер телефона
	 *
	 */
	public static function makeHash(string $phone_number):string {

		return self::_makeHash($phone_number, SALT_PHONE_NUMBER);
	}

	/**
	 * Сравнить хэши
	 *
	 */
	public static function compareHash(string $hash, string $phone_number):bool {

		return self::_compareHash($hash, $phone_number, SALT_PHONE_NUMBER);
	}
}