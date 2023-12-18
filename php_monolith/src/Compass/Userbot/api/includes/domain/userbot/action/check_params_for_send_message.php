<?php

namespace Compass\Userbot;

/**
 * Действие проверки параметров для отправки сообщения
 *
 * Class Domain_Userbot_Action_CheckParamsForSendMessage
 */
class Domain_Userbot_Action_CheckParamsForSendMessage {

	/**
	 * выполняем
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function do(string $type, string|false $text, string|false $file_id):void {

		// проверяем параметры для отправки сообщения
		if ($text === false && $file_id === false) {
			throw new \cs_Userbot_RequestIncorrect("passed empty params");
		}

		// если пришёл некорректный параметр type
		if (!in_array($type, ["text", "file"])) {
			throw new \cs_Userbot_RequestIncorrect("passed incorrect type for request");
		}

		// если передан тип text, но не передан текст для сообщения
		if ($type == "text" && ($text === false || isEmptyString($text))) {
			throw new \cs_Userbot_RequestIncorrect("not passed param text for request");
		}

		// если передан тип file, но не передан id файла
		if ($type == "file" && ($file_id === false || isEmptyString($file_id))) {
			throw new \cs_Userbot_RequestIncorrect("not passed param file_id for request");
		}
	}
}