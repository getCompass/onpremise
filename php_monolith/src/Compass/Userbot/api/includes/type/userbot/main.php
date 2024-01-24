<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * класс для пользовательского бота
 */
class Type_Userbot_Main {

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Получить информацию о боте
	 *
	 * @throws \busException
	 * @throws \cs_Userbot_NotFound
	 * @throws \userAccessException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function get(string $token):Struct_Userbot_Info {

		// получаем информацию от go_userbot_cache
		return Gateway_Bus_UserbotCache::get($token);
	}

	/**
	 * получаем подпись для запроса
	 */
	public static function getApiV1Signature(array $payload, string $token, string $secret_key):string {

		// добавляем токен
		$payload["token"] = $token;

		// сортируем поля
		ksort($payload);

		$payload_json = toJson($payload);

		// получаем подпись
		return hash_hmac("sha256", $payload_json, $secret_key);
	}

	/**
	 * получаем подпись для запроса
	 */
	public static function getApiSignature(string $payload, string $token, string $secret_key):string {

		$temp = $token . $payload;

		// получаем подпись
		return hash_hmac("sha256", $temp, $secret_key);
	}

	/**
	 * проверяем, что подпись корректна
	 *
	 * @throws CaseException
	 */
	public static function assertCorrectSignature(string $signature):void {

		// если пришла некорректная подпись
		if (mb_strlen($signature) != 64) {
			throw new CaseException(CASE_EXCEPTION_CODE_4, "passed invalid signature");
		}
	}
}