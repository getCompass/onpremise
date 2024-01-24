<?php

namespace Compass\Conversation;

/**
 * Проверяем, что сообщение позволяет Напомнить
 */
class Domain_Remind_Action_CheckMessageAllowedForRemind {

	/**
	 * выполняем
	 *
	 * @throws Domain_Conversation_Exception_Message_NotAllowForRemind
	 * @throws \parseException
	 * @throws cs_Message_IsDeleted
	 */
	public static function do(array $message, int $user_id):void {

		// сообщение удалено
		if (Type_Conversation_Message_Main::getHandler($message)::isDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}

		// на сообщение можно добавлять/удалять Напоминание
		if (!Type_Conversation_Message_Main::getHandler($message)::isAllowToRemind($message, $user_id)) {
			throw new Domain_Conversation_Exception_Message_NotAllowForRemind("message type not allow for remind");
		}

		if (Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalRespect($message)) {
			throw new Domain_Conversation_Exception_Message_NotAllowForRemind("message type not allow for remind");
		}

		if (Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalExactingness($message)) {
			throw new Domain_Conversation_Exception_Message_NotAllowForRemind("message type not allow for remind");
		}

		if (Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalAchievement($message)) {
			throw new Domain_Conversation_Exception_Message_NotAllowForRemind("message type not allow for remind");
		}
	}
}
