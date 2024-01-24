<?php

namespace Compass\Company;

/**
 * Класс для хэширования user-agent
 */
class Type_Hash_UserAgent extends Type_Hash_Main {

	/**
	 * Хэшировать user-agent
	 *
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function makeHash(string $ua, int $version = 0):string {

		return self::_makeVersionedHash($ua, SALT_USERAGENT, $version);
	}

	/**
	 * Сравнить хэши
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 */
	public static function compareHash(string $hash, string $ua):bool {

		return self::_compareVersionedHash($hash, $ua, SALT_USERAGENT);
	}
}