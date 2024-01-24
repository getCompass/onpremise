<?php

namespace Compass\Userbot;

/**
 * Действие добавления реакции на сообщение
 *
 * Class Domain_Message_Action_AddReaction
 */
class Domain_Message_Action_AddReaction {

	/**
	 * выполняем
	 *
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_Message_IsNotAllowed
	 * @throws \cs_Reaction_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $userbot_user_id, string $message_key, string $reaction, string $domino_entrypoint, int $company_id):void {

		if (Type_Pack_Message::isFromConversation(Type_Pack_Message::doDecrypt($message_key))) {
			Gateway_Socket_Conversation::addReaction($userbot_user_id, $message_key, $reaction, $domino_entrypoint, $company_id);
		} else {
			Gateway_Socket_Thread::addReaction($userbot_user_id, $message_key, $reaction, $domino_entrypoint, $company_id);
		}
	}
}