<?php

namespace Compass\Conversation;

/**
 * Получить левое меню
 */
class Domain_Conversation_Action_GetLeftMenuOld {

	/**
	 * Получить левое меню
	 *
	 */

	public static function do(int $user_id, int $favorites_filter, int $limit, int $offset, int $unread_only = 0):array {

		return match ($unread_only) {

			0 => Gateway_Db_CompanyConversation_UserLeftMenu::getLeftMenu($user_id, $favorites_filter, $limit, $offset),
			1 => Gateway_Db_CompanyConversation_UserLeftMenu::getUnreadMenu($user_id, $favorites_filter, $limit, $offset),
		};
	}
}