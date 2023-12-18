<?php

namespace Compass\Announcement;

use JetBrains\PhpStorm\Pure;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Type_Socket_Auth_Key {

	/**
	 * получаем подпись
	 *
	 * @param string $private_key
	 * @param string $json_params
	 *
	 * @return string
	 */
	#[Pure]
	public static function getSignature(string $private_key, string $json_params):string {

		// формируем подпись
		return md5($private_key . $json_params);
	}

	/**
	 * проверяем валидность подписи
	 *
	 * @param string $signature
	 * @param string $public_key
	 * @param string $json_params
	 *
	 * @return bool
	 */
	#[Pure]
	public static function isSignatureAgreed(string $signature, string $public_key, string $json_params):bool {

		return $signature == self::getSignature($public_key, $json_params);
	}
}
