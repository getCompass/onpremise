<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\CaseException;

/**
 * Класс для пользовательского бота
 */
class Type_Userbot_Main {

	/**
	 * получаем подпись для запроса
	 */
	public static function getSignature(array $payload, string $token, string $secret_key):string {

		// добавляем токен
		$payload["token"] = $token;

		// сортируем поля
		ksort($payload);

		// получаем подпись
		return hash_hmac("sha256", toJson($payload), $secret_key);
	}

	/**
	 * проверяем, что подпись корректна
	 *
	 * @throws CaseException
	 */
	public static function assertCorrectSignature(string $signature):void {

		// если пришла некорректная подпись
		if (mb_strlen($signature) != 64) {
			throw new CaseException(0, "passed invalid signature");
		}
	}
}