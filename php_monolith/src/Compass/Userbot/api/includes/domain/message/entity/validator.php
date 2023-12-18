<?php

namespace Compass\Userbot;

/**
 * класс для валидации данных сущности сообщений
 *
 * Class Domain_Message_Entity_Validator
 */
class Domain_Message_Entity_Validator {

	/**
	 * проверяем корректность ключа сообщения
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function assertMessageId(string $message_id):void {

		if (isEmptyString($message_id)) {
			throw new \cs_Userbot_RequestIncorrect("passed empty message_id");
		}
	}

	/**
	 * проверяем корректность реакции для сообщения
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function assertReaction(string $reaction):void {

		if (isEmptyString($reaction)) {
			throw new \cs_Userbot_RequestIncorrect("passed empty reaction");
		}
	}
}