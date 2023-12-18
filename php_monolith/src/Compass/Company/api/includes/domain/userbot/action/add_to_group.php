<?php

namespace Compass\Company;

/**
 * Класс action для добавления ботов в группу
 */
class Domain_Userbot_Action_AddToGroup {

	protected const _CONVERSATION_TYPE_GROUP_DEFAULT = 2;

	protected const _ACTION_TYPE_ADD_TO_GROUP = 1;

	/**
	 * выполняем действие
	 */
	public static function do(array $userbot_id_list, string $conversation_map):void {

		// добавляем в группу для связи бота и диалога
		Gateway_Db_CompanyData_UserbotConversationRel::insertList($userbot_id_list, self::_CONVERSATION_TYPE_GROUP_DEFAULT, $conversation_map);

		// добавляем в таблицу с историей действий с диалогом бота
		Gateway_Db_CompanyData_UserbotConversationHistory::insertList($userbot_id_list, self::_ACTION_TYPE_ADD_TO_GROUP, $conversation_map);
	}
}