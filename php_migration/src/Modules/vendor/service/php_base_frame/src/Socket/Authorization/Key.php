<?php

namespace BaseFrame\Socket\Authorization;

use JetBrains\PhpStorm\Pure;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Key {

	/**
	 * получаем подпись
	 *
	 */
	public static function getSignature(string $auth_key, string $json_params):string {

		// формируем подпись
		return md5($auth_key . $json_params);
	}

	/**
	 * проверяем валидность подписи
	 *
	 */
	#[Pure]
	public static function isSignatureAgreed(string $signature, string $public_key, string $json_params):bool {

		return $signature == self::getSignature($public_key, $json_params);
	}
}
