<?php

namespace Compass\Conversation;

/**
 * Действие проверки, что сообщение доступно для пользователя
 */
class Domain_Conversation_Action_Message_CheckAllowMessage {

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 * @throws Domain_Conversation_Exception_Message_NotAllowForUser
	 */
	public static function do(int $user_id, array $message, array $dynamic):void {

		// получаем дату создания сообщения
		$message_created_at = Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message);

		// получаем время, когда диалог был почищен
		$clear_until = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic["user_clear_info"], $dynamic["conversation_clear_info"], $user_id);

		// если сообщение создано раньше, чем очищен диалог
		// или сообщения скрыто для пользователя
		if ($message_created_at < $clear_until || Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
			throw new Domain_Conversation_Exception_Message_NotAllowForUser("User cant get this message");
		}
	}
}