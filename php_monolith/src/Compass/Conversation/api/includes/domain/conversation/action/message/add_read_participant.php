<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Действие для добавления просмотревшего пользователя
 */
class Domain_Conversation_Action_Message_AddReadParticipant {

	/**
	 * выполняем
	 *
	 * @param array $left_menu_row
	 * @param array $need_read_message
	 * @param int   $user_id
	 * @param int   $member_role
	 * @param int   $member_permissions
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function do(array $left_menu_row, array $need_read_message, int $user_id, int $member_role, int $member_permissions):void {

		if (!IS_MESSAGE_READ_PARTICIPANTS_ENABLED || Permission::isReadMessageStatusHidden($member_role, $member_permissions)) {
			return;
		}
		$conversation_map  = $left_menu_row["conversation_map"];
		$conversation_type = $left_menu_row["type"];

		$remind_creator_id      = Type_Conversation_Message_Main::getHandler($need_read_message)::getRemindCreatorUserId($need_read_message);
		$message_created_at     = Type_Conversation_Message_Main::getHandler($need_read_message)::getCreatedAt($need_read_message);
		$message_sender_user_id = Type_Conversation_Message_Main::getHandler($need_read_message)::getSenderUserId($need_read_message);
		$message_map            = Type_Conversation_Message_Main::getHandler($need_read_message)::getMessageMap($need_read_message);

		$is_single = Type_Conversation_Meta::isSubtypeOfSingle($conversation_type);

		$hide_read_participant_list = [$message_sender_user_id];

		if ($is_single && $remind_creator_id !== 0) {
			$hide_read_participant_list[] = $remind_creator_id;
		}

		Gateway_Bus_Company_ReadMessage::add($conversation_map, $user_id, $message_map, $message_created_at, $hide_read_participant_list);
	}
}