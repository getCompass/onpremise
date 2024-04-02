<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Type_Socket_Auth_Handler {

	public const AUTH_TYPE_SSL = 1;
	public const AUTH_TYPE_KEY = 2;

	/**
	 * получаем подпись
	 *
	 * @param int    $auth_type
	 * @param string $private_key
	 * @param string $json_params
	 *
	 * @return string
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function getSignature(int $auth_type, string $private_key, string $json_params):string {

		return match ($auth_type) {

			self::AUTH_TYPE_SSL => Type_Socket_Auth_Ssl::getSignature($private_key, $json_params),
			self::AUTH_TYPE_KEY => Type_Socket_Auth_Key::getSignature($private_key, $json_params),
			default             => throw new ParseFatalException("passed unknown socket auth type"),
		};
	}

	/**
	 * проверяем валидность подписи
	 *
	 * @throws \parseException
	 */
	public static function isSignatureAgreed(int $auth_type, string $signature, string $json_params, string $public_key):bool {

		return match ($auth_type) {

			self::AUTH_TYPE_SSL => Type_Socket_Auth_Ssl::isSignatureAgreed($signature, $public_key, $json_params),
			self::AUTH_TYPE_KEY => Type_Socket_Auth_Key::isSignatureAgreed($signature, $public_key, $json_params),
			default             => throw new ParseFatalException("passed unknown socket auth type"),
		};
	}
}
