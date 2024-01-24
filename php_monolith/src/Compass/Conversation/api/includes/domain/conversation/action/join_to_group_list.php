<?php

namespace Compass\Conversation;

/**
 * Action для добавления пользователя в список групп
 */
class Domain_Conversation_Action_JoinToGroupList {

	/**
	 * выполняем action
	 *
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function do(int $user_id, array $conversation_map_list):void {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		$user_info      = $user_info_list[$user_id];

		foreach ($conversation_map_list as $conversation_map) {
			Helper_Groups::doJoin($conversation_map, $user_id, $user_info->role, $user_info->permissions);
		}
	}
}