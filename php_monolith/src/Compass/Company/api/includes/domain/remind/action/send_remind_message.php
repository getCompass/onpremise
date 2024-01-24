<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * action для отправки сообщения-Напоминания
 */
class Domain_Remind_Action_SendRemindMessage {

	/**
	 * выполняем
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(Struct_Db_CompanyData_Remind $remind):void {

		// в зависимости от типа Напоминания (в чат, в тред, родительский в тред) создаём Напоминание в чате/треде
		switch ($remind->type) {

			case Domain_Remind_Entity_Remind::CONVERSATION_MESSAGE_TYPE:

				// отправляем сокет-запрос на создание сообщения-Напоминание и отправку в чат
				$comment = Domain_Remind_Entity_Remind::getComment($remind->data);
				Gateway_Socket_Conversation::sendRemindMessage($remind->remind_id, $remind->recipient_id, $comment);
				break;

			case Domain_Remind_Entity_Remind::THREAD_MESSAGE_TYPE:
			case Domain_Remind_Entity_Remind::THREAD_PARENT_MESSAGE_TYPE:

				// отправляем сокет-запрос на создание сообщения-Напоминание и отправку в тред
				$comment = Domain_Remind_Entity_Remind::getComment($remind->data);
				Gateway_Socket_Thread::sendRemindMessage($remind->remind_id, $remind->recipient_id, $remind->creator_user_id, $comment, $remind->type);
				break;

			default:
				throw new \BaseFrame\Exception\Domain\ParseFatalException("unknown remind type for event sendRemindMessage");
		}
	}
}