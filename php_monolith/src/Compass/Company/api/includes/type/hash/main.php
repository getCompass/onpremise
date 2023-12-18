<?php

namespace Compass\Company;

/**
 * Класс для работы с хэшами
 */
abstract class Type_Hash_Main {

	/**
	 * Хэшируем значение
	 */
	protected static function _makeHash(mixed $value, string $salt):string {

		return hash_hmac("sha1", (string) $value, $salt);
	}

	/**
	 * Метод для хэширования строки с версией
	 *
	 * @throws cs_IncorrectSaltVersion
	 */
	protected static function _makeVersionedHash(mixed $value, array $salt_array, int $version = 0):string {

		if ($version === 0) {
			$version = max(array_keys($salt_array));
		}
		if (!isset($salt_array[$version])) {
			throw new cs_IncorrectSaltVersion();
		}
		$hash = self::_makeHash($value, $salt_array[$version]);
		return $version . "_" . $hash;
	}

	/**
	 * Проверка хэша
	 */
	protected static function _compareHash(string $hash, mixed $value, string $salt):bool {

		return hash_equals($hash, self::_makeHash($value, $salt));
	}

	/**
	 * Проверяем, совпадают ли хэши с версией
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 */
	protected static function _compareVersionedHash(string $original_hash, mixed $value, array $salt_array):bool {

		$hash_parts = explode("_", $original_hash);
		if (count($hash_parts) !== 2) {
			throw new cs_InvalidHashStruct();
		}

		$version = $hash_parts[0];

		$comparable_hash = self::_makeVersionedHash($value, $salt_array, $version);

		return hash_equals($original_hash, $comparable_hash);
	}
}