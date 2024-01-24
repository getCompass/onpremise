<?php

namespace Compass\Conversation;

/**
 * класс для валидации данных конфига
 */
class Domain_Conversation_Entity_Config {

	public const NOTES_AVATAR_FILE_KEY_NAME   = "notes_avatar_file_key";
	public const SUPPORT_AVATAR_FILE_KEY_NAME = "support_avatar_file_key";

	/**
	 * Выбрасывает исключение когда значение невалидно
	 *
	 * @throws cs_InvalidConfigValue
	 */
	public static function assertConfigValueNotValid(string $value):void {

		if (mb_strlen($value) < 1) {
			throw new cs_InvalidConfigValue();
		}
	}
}
