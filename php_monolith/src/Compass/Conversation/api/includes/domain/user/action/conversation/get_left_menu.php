<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Action получить левое меню
 */
class Domain_User_Action_Conversation_GetLeftMenu {

	/**
	 *
	 * @param int $user_id
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function do(int $user_id, int $limit, int $offset):array {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		if (!isset($user_info_list[$user_id])) {
			throw new ReturnFatalException("user not found");
		}

		$left_menu_list = Type_Conversation_LeftMenu::getByOffset($user_id, $limit, $offset);

		// оставляем только conversation_map чтобы гонять меньше данных
		$output = [];
		foreach ($left_menu_list as $left_menu_row) {
			$output[] = $left_menu_row["conversation_map"];
		}

		return $output;
	}
}
