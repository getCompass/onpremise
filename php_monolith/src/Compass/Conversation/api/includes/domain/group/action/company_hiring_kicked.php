<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Action для кика пользователя из группы найма и увольнения компании
 */
class Domain_Group_Action_CompanyHiringKicked {

	/**
	 * убираем пользователя из группы найма и увольнения компании
	 *
	 * @param int $user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function do(int $user_id):void {

		// список групп
		$group_list = Domain_Group_Entity_Company::HIRING_GROUP_LIST_ON_ADD_MEMBER;

		// кикаем пользователя из каждой группы
		foreach ($group_list as $group_key_name) {

			try {
				self::_doKick($user_id, $group_key_name);
			} catch (\cs_RowIsEmpty) {
				return;
			}
		}
	}

	/**
	 * кикаем пользователя из чата Найма
	 *
	 * @param int    $user_id
	 * @param string $group_key_name
	 *
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @long
	 */
	protected static function _doKick(int $user_id, string $group_key_name):void {

		// пробуем получить ключ дефолтной группы
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMapByKey($group_key_name);
		if (mb_strlen($conversation_map) < 1) {
			return;
		}

		try {

			// убираем пользователя из группы
			[$after_meta_row, $before_meta_row] = Type_Conversation_Group::removeUserFromGroup(
				$conversation_map, $user_id, Type_Conversation_LeftMenu::LEAVE_REASON_KICKED
			);
		} catch (cs_UserIsNotMember) {
			return;
		}

		if (defined("IS_HIRING_SYSTEM_MESSAGES_ENABLED") && (bool) IS_HIRING_SYSTEM_MESSAGES_ENABLED) {

			// отправляем системное сообщение в диалог, что пользователь покинул группу
			$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserKickedFromGroup($user_id);
			Type_Phphooker_Main::addMessage(
				$conversation_map, $system_message, $after_meta_row["users"], $after_meta_row["type"], $after_meta_row["conversation_name"], $after_meta_row["extra"]
			);
		}

		// обновляем badge с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$conversation_map], true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);

		// обновляем количество пользователей в left_menu, очищаем meta кэш и отправляем событие пользователю, что покинул диалог в левом меню
		Type_Phphooker_Main::updateMembersCount($conversation_map, $after_meta_row["users"]);
		Type_Phphooker_Main::sendClearThreadMetaCache($conversation_map);

		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($before_meta_row["users"]);
		Gateway_Bus_Sender::conversationGroupUserLeaved(
			$talking_user_list, $conversation_map, $user_id, $after_meta_row["users"], Type_Conversation_LeftMenu::LEAVE_REASON_KICKED
		);
	}
}
