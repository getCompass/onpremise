<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Action для добавления пользователя в группу найма и увольнения компании
 */
class Domain_Group_Action_CompanyHiringAdd {

	/**
	 * добавляем пользователя в группу найма и увольнения компании
	 *
	 * @param int  $user_id
	 * @param int  $role
	 * @param bool $is_company_creator
	 *
	 * @throws ParseFatalException
	 */
	public static function do(int $user_id, int $role, bool $is_company_creator = false):void {

		// дефолтные группы для компании
		$group_list = Domain_Group_Entity_Company::HIRING_GROUP_LIST_ON_ADD_MEMBER;

		// добавляем пользователя в каждую группу из списка
		foreach ($group_list as $group_key_name) {

			try {
				self::_tryAddGroup($user_id, $role, $group_key_name, $is_company_creator);
			} catch (\cs_RowIsEmpty) {
				return;
			}
		}
	}

	/**
	 * пробуем добавить в указанную группу
	 *
	 * @param int    $user_id
	 * @param int    $role
	 * @param string $group_key_name
	 * @param bool   $is_company_creator
	 *
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _tryAddGroup(int $user_id, int $role, string $group_key_name, bool $is_company_creator):void {

		// пробуем получить ключ дефолтной группы
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMapByKey($group_key_name);
		if (mb_strlen($conversation_map) < 1) {
			return;
		}

		// добавляем пользователя в группу
		[$conversation_data, $is_already_was_joined] = Type_Conversation_Group::addUserToGroup($conversation_map, $user_id, $role, true);
		$meta_row = $conversation_data["meta_row"];

		// обновляем количество пользователей в left_menu, очищаем meta кэш
		Type_Phphooker_Main::updateMembersCount($conversation_map, $meta_row["users"]);
		Type_Phphooker_Main::sendClearThreadMetaCache($conversation_map);

		// если пользователь не оказался уже добавленным в чат
		if ($is_already_was_joined == false) {

			if (defined("IS_HIRING_SYSTEM_MESSAGES_ENABLED") && (bool) IS_HIRING_SYSTEM_MESSAGES_ENABLED && !$is_company_creator)  {

				// отправляем системное сообщение в группу о вступлении
				$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserJoinedToGroup($user_id);
				Type_Phphooker_Main::addMessage(
					$conversation_map, $system_message, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"]
				);
			}
		}
	}
}
