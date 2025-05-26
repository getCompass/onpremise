<?php

namespace BaseFrame\System;

use BaseFrame\Crypt\CryptProvider;
use BaseFrame\Exception\Domain\DecryptFailed;

/**
 * Класс для шифрования и расшифрования значений
 */
class Crypt {

	/**
	 * Зашифровать значение
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function encrypt(string $value):string {

		$iv_length      = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv             = substr(CryptProvider::extended()->vector(), 0, $iv_length);
		$ciphertext_raw = openssl_encrypt($value, ENCRYPT_CIPHER_METHOD, CryptProvider::extended()->key(), OPENSSL_RAW_DATA, $iv);
		$hmac           = hash_hmac("sha256", $ciphertext_raw, CryptProvider::extended()->key(), true);
		return base64_encode($hmac . $ciphertext_raw);
	}

	/**
	 * Расшифровать значение
	 *
	 * @param string $encrypted_value
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function decrypt(string $encrypted_value):string {

		$encrypted_value     = base64_decode($encrypted_value);
		$ivlen               = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv                  = substr(CryptProvider::extended()->vector(), 0, $ivlen);
		$hmac                = substr($encrypted_value, 0, $sha2len = 32);
		$encrypted_value_raw = substr($encrypted_value, $sha2len);
		$decrypted_value     = openssl_decrypt($encrypted_value_raw, ENCRYPT_CIPHER_METHOD, CryptProvider::extended()->key(), OPENSSL_RAW_DATA, $iv);

		$calc_mac = hash_hmac("sha256", $encrypted_value_raw, CryptProvider::extended()->key(), true);

		if (!hash_equals($hmac, $calc_mac)) {
			throw new DecryptFailed("decrypt was failed");
		}

		return $decrypted_value;
	}

	/**
	 * Сделать хэш из значения
	 *
	 * @param string $value
	 * @param string $salt
	 *
	 * @return string
	 */
	public static function makeHash(string $value, string $salt):string {

		return sha1($value . $salt);
	}
}