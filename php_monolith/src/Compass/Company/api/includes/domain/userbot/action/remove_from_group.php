<?php

namespace Compass\Company;

/**
 * Класс action для удаления бота из группы
 */
class Domain_Userbot_Action_RemoveFromGroup {

	protected const _ACTION_TYPE_REMOVE_FROM_GROUP = 2;

	/**
	 * выполняем действие
	 */
	public static function do(string $userbot_id, string $conversation_map):void {

		Gateway_Db_CompanyData_UserbotConversationRel::delete($userbot_id, $conversation_map);

		Gateway_Db_CompanyData_UserbotConversationHistory::insertList([$userbot_id], self::_ACTION_TYPE_REMOVE_FROM_GROUP, $conversation_map);
	}
}