<?php

namespace Compass\Company;

/**
 * Класс для работы с webhook бота
 */
class Domain_Userbot_Entity_Webhook {

	/**
	 * это запрос бота версии 2?
	 */
	public static function isUserbotRequestV2():bool {

		// если переданы в заголовках сигнатура и токен бота
		return !isEmptyString(getHeader("HTTP_SIGNATURE")) && !isEmptyString(getHeader("HTTP_AUTHORIZATION"));
	}

	/**
	 * это запрос бота версии 3?
	 */
	public static function isUserbotRequestV3():bool {

		// если переданы в заголовках токен бота
		return !isEmptyString(getHeader("HTTP_AUTHORIZATION"));
	}

	/**
	 * получаем переданные post-данные
	 */
	public static function getPostData():array {

		$payload = file_get_contents("php://input");
		return fromJson($payload);
	}

	/**
	 * получаем данные бота из переданных заголовков
	 */
	public static function getAuthorizationData():string {

		// ожидаем заголовок формата "Authorization: bearer=<токен бота>"
		$header_for_token = getHeader("HTTP_AUTHORIZATION");
		$tmp              = explode("=", $header_for_token);

		if (count($tmp) != 2) {

			// ожидаем заголовок формата "Authorization: Bearer <токен бота>"
			$tmp = explode(" ", $header_for_token);
			if (count($tmp) != 2) {
				return "";
			}
		}

		if (count($tmp) != 2 || trim(mb_strtolower($tmp[0])) != "bearer") {
			return "";
		}

		return trim($tmp[1]);
	}
}