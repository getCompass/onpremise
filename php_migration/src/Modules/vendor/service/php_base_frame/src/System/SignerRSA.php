<?php

namespace BaseFrame\System;

use OpenSSLAsymmetricKey;

// класс для подписи и сверки подписей через openssl

/**
 * Класс для подписи и сверки подписей через openssl
 */
class SignerRSA {

	//
	protected const _OPENSSL_ALGORITHM = "sha256WithRSAEncryption";

	private function __construct(
		private ?OpenSSLAsymmetricKey $_private_key,
		private ?OpenSSLAsymmetricKey $_public_key
	) {
	}

	// подписываем
	public function sign(string $data, bool $output_as_hex = true):?string {

		$signature = "";
		$success   = openssl_sign($data, $signature, $this->_private_key, self::_OPENSSL_ALGORITHM);
		if (!$success) {
			return null;
		}
		return $output_as_hex ? bin2hex($signature) : $signature;
	}

	// сверяем подпись - hex
	public function verifyHex(string $data, string $signature):bool {

		return $this->verify($data, hex2bin($signature));
	}

	// сверяем подпись - base64
	public function verifyBase64(string $data, string $signature):bool {

		return $this->verify($data, base64_decode($signature));
	}

	// сверяем подпись - binary
	public function verify(string $data, string $signature):bool {

		return openssl_verify($data, $signature, $this->_public_key, self::_OPENSSL_ALGORITHM) === 1;
	}

	// -------------------------------------------------------
	// INIT
	// -------------------------------------------------------

	// init by private_key
	public static function initByPrivateKey(string $private_key_string):self {

		$private_key = self::makePrivateKey($private_key_string);
		$public_key  = self::makePublicKeyFromPrivate($private_key);
		return new self($private_key, $public_key);
	}

	// init by public_key
	public static function initByPublicKey(string $public_key_string):self {

		$public_key = self::makePublicKey($public_key_string);
		return new self(null, $public_key);
	}

	// true если ключ отформатирован как надо
	public static function isRsaKeyFormatted(string $key):string {

		return inHtml($key, "KEY-----");
	}

	// -------------------------------------------------------
	// PRIVATE KEY
	// -------------------------------------------------------

	// формируем объект private_key
	public static function makePrivateKey(string $private_key_string):?OpenSSLAsymmetricKey {

		if (!self::isRsaKeyFormatted($private_key_string)) {
			$private_key_string = self::formatPrivateKey($private_key_string);
		}

		$key = openssl_get_privatekey($private_key_string);
		if ($key === false) {
			return null;
		}
		return $key;
	}

	// форматируем private_key в нормальный формат
	public static function formatPrivateKey(string $key):string {

		$prefix  = "-----BEGIN RSA PRIVATE KEY-----\n";
		$postfix = "-----END RSA PRIVATE KEY-----";
		return $prefix . chunk_split($key, 64, "\n") . $postfix;
	}

	// -------------------------------------------------------
	// PUBLIC KEY
	// -------------------------------------------------------

	// формируем объект public_key
	public static function makePublicKey(string $key_string):?OpenSSLAsymmetricKey {

		if (!self::isRsaKeyFormatted($key_string)) {
			$key_string = self::formatPublicKey($key_string);
		}

		$key = openssl_get_publickey($key_string);
		if ($key === false) {
			return null;
		}
		return $key;
	}

	// формируем строку public_key из приватного ключа - строки
	public static function makePublicKeyStringFromPrivateString(string $private_key_string):?string {

		$private_key = self::makePrivateKey($private_key_string);
		if (is_null($private_key)) {
			return null;
		}
		return openssl_pkey_get_details($private_key)["key"];
	}

	// формируем строку public_key из приватного ключа - строки
	public static function makePublicKeyFromPrivateString(string $private_key_string):?OpenSSLAsymmetricKey {

		$public_key_string = self:: makePublicKeyStringFromPrivateString($private_key_string);
		if (is_null($public_key_string)) {
			return null;
		}
		return self::makePublicKey($public_key_string);
	}

	// формируем объект public_key из приватного ключа
	public static function makePublicKeyFromPrivate(OpenSSLAsymmetricKey $private_key):OpenSSLAsymmetricKey {

		$pem_public_key = openssl_pkey_get_details($private_key)["key"];
		return openssl_pkey_get_public($pem_public_key);
	}

	// форматируем public в нормальный формат
	public static function formatPublicKey(string $key):string {

		$prefix  = "-----BEGIN PUBLIC KEY-----\n";
		$postfix = "-----END PUBLIC KEY-----";
		return $prefix . chunk_split($key, 64, "\n") . $postfix;
	}

}