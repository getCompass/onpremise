<?php

namespace Compass\Conversation;

/**
 * Класс для взаимодействия с ботами
 */
class Domain_Userbot_Entity_Userbot {

	// список статусов бота
	public const STATUS_DISABLE = 0; // бот отключён
	public const STATUS_ENABLE  = 1; // бот активен
	public const STATUS_DELETE  = 2; // бот удалён

	/**
	 * текст сообщения имеет формат команды?
	 */
	public static function isFormatCommand(string $message_text):bool {

		return mb_substr($message_text, 0, 1) == "/";
	}
}