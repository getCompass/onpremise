<?php

namespace Compass\Conversation;

/**
 *
 * Получить количество непрочитанных чатов и сообщений
 */
class Domain_Conversation_Action_GetTotalUnreadCount {

	/**
	 * Получить количество непрочитанных чатов и сообщений
	 *
	 */
	public static function do(int $user_id):array {

		// получаем количество непрочитанных диалога
		$dynamic_row = Gateway_Db_CompanyConversation_UserInbox::getOne($user_id);

		if (!isset($dynamic_row["user_id"])) {

			return [
				"conversations_unread_count" => 0,
				"messages_unread_count"      => 0,
			];
		}

		return [
			"conversations_unread_count" => (int) $dynamic_row["conversation_unread_count"],
			"messages_unread_count"      => (int) $dynamic_row["message_unread_count"],
		];
	}
}