<?php

namespace Compass\Pivot;

/**
 * Класс для хэширования кода
 */
class Type_Hash_Code extends Type_Hash_Main {

	/**
	 * Хэшировать кода
	 *
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function makeHash(string|int $code, int $version = 0):string {

		return self::_makeVersionedHash($code, SALT_CODE, $version);
	}

	/**
	 * Сравнить хэши
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 */
	public static function compareHash(string $hash, string|int $code):bool {

		return self::_compareVersionedHash($hash, $code, SALT_CODE);
	}
}