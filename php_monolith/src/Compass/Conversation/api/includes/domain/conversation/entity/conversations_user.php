<?php

namespace Compass\Conversation;

/**
 * Класс для работы с диалогами пользователя
 */
class Domain_Conversation_Entity_ConversationsUser {

	/**
	 * Получим все диалоги пользователя
	 *
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function getConversationsByUserId(int $user_id, int $limit = 500, int $offset = 0):array {

		// получаем все диалоги пользователя и дополняем массив с диалогами
		$conversation_list = Domain_User_Action_Conversation_GetLeftMenu::do($user_id, $limit, $offset);

		return $conversation_list;
	}
}
