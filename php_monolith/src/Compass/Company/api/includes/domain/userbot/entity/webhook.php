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
		return count($tmp) != 2 || $tmp[0] != "bearer" ? "" : $tmp[1];
	}
}