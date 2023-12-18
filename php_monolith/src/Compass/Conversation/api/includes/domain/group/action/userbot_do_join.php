<?php

namespace Compass\Conversation;

use CompassApp\Domain\Member\Entity\Member;

/**
 * Action для вступления пользовательского бота в группу
 */
class Domain_Group_Action_UserbotDoJoin {

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 */
	public static function do(string $conversation_map, int $userbot_user_id, string $userbot_id, bool $is_need_welcome_message):void {

		// добавляем пользователя в группу
		[$conversation_data] = Type_Conversation_Group::addUserToGroup(
			$conversation_map, $userbot_user_id, Type_Conversation_Meta_Users::ROLE_DEFAULT, false, false, $userbot_id
		);
		$meta_row      = Type_Conversation_Utils::getMetaRowFromConversationData($conversation_data);
		$left_menu_row = Type_Conversation_Utils::getLeftMenuRowFromConversationData($conversation_data);

		Helper_Groups::setClearMessagesConversationForJoinGroup($userbot_user_id, Member::ROLE_USERBOT, $conversation_map, $left_menu_row, $meta_row);

		$message_list = [];

		// добавляем системное сообщение о вступлении в группу
		$message_list[] = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserJoinedToGroup($userbot_user_id);
		$is_silent      = Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnInviteAndJoin($meta_row["extra"]);

		// если необходимо приветственное сообщение от бота
		if ($is_need_welcome_message) {

			$userbot_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeUserbotWelcomeMessage($userbot_user_id);
			$message_list[]  = Type_Conversation_Message_Main::getHandler($userbot_message)::setUserbotSender($userbot_message);
		}

		// отправляем сообщения
		Type_Phphooker_Main::addMessageList(
			$conversation_map, $message_list, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"], $is_silent
		);

		// действия после вступления бота в групповой диалог
		self::_onJoinUserToGroup($conversation_map, $userbot_user_id, $meta_row["users"], Type_Conversation_Meta_Users::ROLE_DEFAULT);
	}

	/**
	 * действия после вступления в группу
	 *
	 * @throws \parseException
	 */
	protected static function _onJoinUserToGroup(string $conversation_map, int $user_id, array $users, int $role):void {

		// обновляем количество пользователей в left_menu, очищаем meta кэш
		Type_Phphooker_Main::updateMembersCount($conversation_map, $users);
		Type_Phphooker_Main::sendClearThreadMetaCache($conversation_map);

		// отправляем событие о вступлении нового участника
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($users);
		Gateway_Bus_Sender::conversationGroupUserJoined($talking_user_list, $conversation_map, $user_id, $role, $users);
	}
}
