<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Действие проверки для отправки сообщения-Напоминания
 */
class Domain_Conversation_Action_Message_CheckForSendRemind {

	/**
	 * выполняем
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws cs_Message_IsDeleted
	 */
	public static function do(array $original_message, int $sender_user_id, int $remind_created_at, int $clear_until_for_all):void {

		// проверяем, что сообщение уже не удалено
		if (Type_Conversation_Message_Main::getHandler($original_message)::isMessageDeleted($original_message)) {
			throw new cs_Message_IsDeleted();
		}

		// проверяем, что можем напоминать сообщение-оригинал
		if (!Type_Conversation_Message_Main::getHandler($original_message)::isAllowToRemind($original_message, $sender_user_id)) {
			throw new ParseFatalException("you have not permissions to remind this message");
		}

		// проверяем время очистки диалога у всех
		if ($remind_created_at <= $clear_until_for_all) {
			throw new Domain_Remind_Exception_AlreadyRemoved("remind already removed - conversation clear all");
		}
	}
}