<?php

namespace Compass\Conversation;

/**
 * Проверим что пользователя действительно удалили
 */
class Domain_Conversation_Action_CheckClearConversations {

	/**
	 * Проверим что пользователя действительно удалили
	 *
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function run(int $user_id, int $limit, int $offset):bool {

		// получим все map диалогов которые есть у пользователя
		$conversation_list = Domain_Conversation_Entity_ConversationsUser::getConversationsByUserId($user_id, $limit, $offset);

		// среди map проверим что диалоги скрыты
		$conversation_list_visible = Type_Conversation_LeftMenu::getListFromLeftMenu($user_id, $conversation_list);

		// если диалогов меньше чем лимит - значит проверили последние
		if (count($conversation_list) < $limit && count($conversation_list_visible) == 0) {
			return true;
		}

		return false;
	}
}
