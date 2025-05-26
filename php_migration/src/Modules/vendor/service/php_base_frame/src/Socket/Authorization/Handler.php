<?php

namespace BaseFrame\Socket\Authorization;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Handler {

	public const AUTH_TYPE_SSL  = 1;
	public const AUTH_TYPE_KEY  = 2;
	public const AUTH_TYPE_NONE = 3;

	/**
	 * получаем подпись
	 *
	 * @param int    $auth_type
	 * @param string $private_key
	 * @param string $json_params
	 *
	 * @return string
	 * @throws ParseFatalException
	 * @throws \returnException
	 */
	public static function getSignature(int $auth_type, string $private_key, string $json_params):string {

		return match ($auth_type) {

			self::AUTH_TYPE_SSL => SSL::getSignature($private_key, $json_params),
			self::AUTH_TYPE_KEY => Key::getSignature($private_key, $json_params),
			self::AUTH_TYPE_NONE => "",
			default => throw new ParseFatalException("passed unknown socket auth type"),
		};
	}

	/**
	 * проверяем валидность подписи
	 *
	 * @throws \parseException
	 */
	public static function isSignatureAgreed(int $auth_type, string $signature, string $json_params, string $public_key, bool $is_local):bool {

		return match ($auth_type) {

			self::AUTH_TYPE_SSL => SSL::isSignatureAgreed($signature, $public_key, $json_params),
			self::AUTH_TYPE_KEY => Key::isSignatureAgreed($signature, $public_key, $json_params),
			self::AUTH_TYPE_NONE => $is_local,
			default => throw new ParseFatalException("passed unknown socket auth type"),
		};
	}
}
