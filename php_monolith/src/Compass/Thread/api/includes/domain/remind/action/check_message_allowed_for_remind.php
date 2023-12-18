<?php

namespace Compass\Thread;

/**
 * Проверяем, что сообщение позволяет Напомнить
 */
class Domain_Remind_Action_CheckMessageAllowedForRemind {

	/**
	 * выполняем
	 *
	 * @throws cs_Message_IsDeleted
	 * @throws Domain_Thread_Exception_Message_NotAllowForRemind
	 * @throws \parseException
	 */
	public static function do(array $message, int $user_id):void {

		// сообщение удалено
		if (Type_Thread_Message_Main::getHandler($message)::isMessageDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}

		// на сообщение можно добавлять/удалять Напоминание
		if (!Type_Thread_Message_Main::getHandler($message)::isAllowToRemind($message, $user_id)) {
			throw new Domain_Thread_Exception_Message_NotAllowForRemind("message type not allow for remind");
		}
	}
}
