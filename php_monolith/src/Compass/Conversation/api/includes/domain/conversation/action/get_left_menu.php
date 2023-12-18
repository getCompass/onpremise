<?php

namespace Compass\Conversation;

/**
 * Получить левое меню
 */
class Domain_Conversation_Action_GetLeftMenu {

	/**
	 * Получить левое меню
	 *
	 * @param int    $user_id
	 * @param int    $limit
	 * @param int    $offset
	 * @param string $search_query
	 * @param int    $filter_favorite
	 * @param int    $filter_unread
	 * @param int    $filter_single
	 * @param int    $filter_unblocked
	 * @param int    $filter_owner
	 * @param int    $filter_system
	 * @param int    $filter_support
	 * @param int    $is_mentioned_first
	 *
	 * @return array
	 */
	public static function do(int $user_id, int $limit, int $offset, string $search_query = "", int $filter_favorite = 0, int $filter_unread = 0,
					  int $filter_single = 0, int $filter_unblocked = 0, int $filter_owner = 0, int $filter_system = 0, int $filter_support = 0, int $is_mentioned_first = 0):array {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getLeftMenuWithFlags($user_id, $limit, $offset, $search_query, $filter_favorite, $filter_unread,
			$filter_single, $filter_unblocked, $filter_owner, $filter_system, $filter_support, $is_mentioned_first);
	}
}
