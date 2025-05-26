<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс action для генерации private и public ключей smart app
 */
class Domain_SmartApp_Action_GenerateSmartAppKeys {

	// конфигурация для генерации RSA ключей (2048 бит)
	protected const _CONFIG = [
		"digest_alg"       => "sha256",
		"private_key_bits" => 2048,
		"private_key_type" => OPENSSL_KEYTYPE_RSA,
	];

	/**
	 * выполняем действие
	 *
	 * @throws ReturnFatalException
	 */
	public static function do():array {

		// генерируем пару ключей
		$key_resource = openssl_pkey_new(self::_CONFIG);
		if (!$key_resource) {
			throw new ReturnFatalException("unhandled error");
		}

		// экспортируем приватный ключ в строку (PEM-формат)
		openssl_pkey_export($key_resource, $private_key);

		// извлекаем публичный ключа из сгенерированной пары
		$key_details = openssl_pkey_get_details($key_resource);
		$public_key  = $key_details["key"];

		// делаем trim
		$public_key_one_line  = trim($public_key);
		$private_key_one_line = trim($private_key);

		return [$public_key_one_line, $private_key_one_line];
	}
}