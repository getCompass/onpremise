<?php

namespace Compass\Conversation;

/**
 * Обновить запись левого меню
 */
class Domain_User_Action_Conversation_UpdateLeftMenu {

	/**
	 * Обновить запись левого меню
	 */
	public static function do(int $user_id, string $conversation_map, array $set, bool $is_version_update_need = true):int {

		if ($is_version_update_need) {

			$previous_version = isset($set["version"]) ? $set["version"] : 0;
			$set["version"]   = Domain_User_Entity_Conversation_LeftMenu::generateVersion($previous_version);
		}

		Gateway_Db_CompanyConversation_UserLeftMenu::set($user_id, $conversation_map, $set);

		return $set["version"];
	}

	/**
	 * Обновить запись левого меню для нескольких пользователей, но одного группового диалога
	 */
	public static function doUserIdList(array $user_id_list, string $conversation_map, array $set, bool $is_version_update_need = true):int {

		if ($is_version_update_need) {

			$previous_version = isset($set["version"]) ? $set["version"] : 0;
			$set["version"]   = Domain_User_Entity_Conversation_LeftMenu::generateVersion($previous_version);
		}

		Gateway_Db_CompanyConversation_UserLeftMenu::setUserIdList($user_id_list, $conversation_map, $set);
		return $set["version"];
	}
}