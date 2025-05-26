<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Pack\Message\Thread;

/**
 * Действие для добавления просмотревшего пользователя
 */
class Domain_Thread_Action_Message_AddReadParticipant {

	/**
	 * выполняем
	 *
	 * @param string $thread_map
	 * @param string $message_map
	 * @param int    $user_id
	 * @param int    $member_role
	 * @param int    $member_permissions
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function do(string $thread_map, string $message_map, int $user_id, int $member_role, int $member_permissions):void {

		if (!IS_MESSAGE_READ_PARTICIPANTS_ENABLED || Permission::isReadMessageStatusHidden($member_role, $member_permissions)) {
			return;
		}

		$meta_row = Gateway_Db_CompanyThread_ThreadMeta::getOne($thread_map);
		[$_, $_, $_, $location_type] = Type_Thread_SourceParentDynamic::get($meta_row["source_parent_rel"]);

		$block_row              = Gateway_Db_CompanyThread_MessageBlock::getOne($thread_map, Thread::getBlockId($message_map));
		$message                = Type_Thread_Message_Block::getMessage($message_map, $block_row);
		$message_created_at     = Type_Thread_Message_Main::getHandler($message)::getMessageCreatedAt($message);
		$remind_creator_id      = Type_Thread_Message_Main::getHandler($message)::getRemindCreatorUserId($message);
		$message_sender_user_id = Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message);

		$is_single = Type_Thread_SourceParentDynamic::isSubtypeOfSingle($location_type);
		$hide_read_participant_list = [$message_sender_user_id];

		if ($is_single && $remind_creator_id !== 0) {
			$hide_read_participant_list[] = $remind_creator_id;
		}

		Gateway_Bus_Company_ReadMessage::add($thread_map, $user_id, $message_map, $message_created_at, $hide_read_participant_list);
	}
}