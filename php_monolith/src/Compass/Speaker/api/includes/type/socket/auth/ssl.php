<?php

namespace Compass\Speaker;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Type_Socket_Auth_Ssl {

	const SSL_ALGORITHM = OPENSSL_ALGO_SHA256;

	/**
	 * получаем подпись
	 *
	 * @throws \returnException
	 */
	public static function getSignature(string $private_key, string $json_params):string {

		// вычисляем подпись
		$is_ok = openssl_sign($json_params, $signature, $private_key, self::SSL_ALGORITHM);
		if (!$is_ok) {
			throw new \returnException("could not get the signature, error: " . openssl_error_string() . "");
		}
		return $signature;
	}

	/**
	 * проверяем валидность подписи
	 *
	 */
	public static function isSignatureAgreed(string $signature, string $public_key, string $json_params):bool {

		return openssl_verify($json_params, $signature, $public_key, OPENSSL_ALGO_SHA256);
	}
}
