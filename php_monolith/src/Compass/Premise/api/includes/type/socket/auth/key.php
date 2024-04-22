<?php

namespace Compass\Premise;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Type_Socket_Auth_Key {

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
	public static function isSignatureAgreed(string $signature, string $public_key, string $json_params):bool {

		return $signature == self::getSignature($public_key, $json_params);
	}
}
