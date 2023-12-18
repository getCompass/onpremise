<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Действие проверки для отправки сообщения-Напоминания
 */
class Domain_Thread_Action_Message_CheckForSendRemind {

	/**
	 * выполняем
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws cs_Message_IsDeleted
	 */
	public static function do(array $original_message, int $sender_user_id):void {

		// проверяем, что сообщение уже не удалено
		if (Type_Thread_Message_Main::getHandler($original_message)::isMessageDeleted($original_message)) {
			throw new cs_Message_IsDeleted();
		}

		// проверяем, что можем напоминать сообщение-оригинал
		if (!Type_Thread_Message_Main::getHandler($original_message)::isAllowToRemind($original_message, $sender_user_id)) {
			throw new ParseFatalException("you have not permissions to remind this message");
		}
	}
}