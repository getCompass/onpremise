<?php

namespace Compass\Conversation;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Action получить левое меню по версии
 */
class Domain_User_Action_Conversation_GetLeftMenuMeta {

	/**
	 *
	 * Получить левое меню по версии
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	#[ArrayShape(["messages_unread_count" => "int", "conversations_unread_count" => "int", "single_conversations_unread_count" => "int", "left_menu_version" => "int"])]
	public static function do(int $user_id):array {

		// получаем счетчики непрочитанных
		$dynamic_row       = Gateway_Db_CompanyConversation_UserInbox::getOne($user_id);
		$left_menu_version = Gateway_Db_CompanyConversation_UserLeftMenu::getLeftMenuLastVersion($user_id);

		return [
			"messages_unread_count"             => (int) ($dynamic_row["message_unread_count"] ?? 0),
			"conversations_unread_count"        => (int) ($dynamic_row["conversation_unread_count"] ?? 0),
			"single_conversations_unread_count" => (int) ($dynamic_row["single_conversation_unread_count"] ?? 0),
			"left_menu_version"                 => (int) $left_menu_version,
		];
	}
}
