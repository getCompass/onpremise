<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;
use CompassApp\Domain\Member\Struct\Short;

/**
 * Экшен для отправки инвайта со статусом "auto_accepted"
 */
class Domain_Invite_Action_SendAutoAccepted {

	/**
	 * выполнение процесса
	 *
	 * @param int    $sender_user_id
	 * @param Short  $member
	 * @param array  $group_meta_row
	 * @param string $platform
	 * @param bool   $is_async_send_invite_message
	 *
	 * @throws BusFatalException
	 * @throws Domain_Conversation_Exception_Guest_AttemptInitialConversation
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws Domain_Invite_Exception_IsNotHuman
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BlockException
	 * @throws ControllerMethodNotFoundException
	 * @throws \ErrorException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 * @throws cs_ErrorSocketRequest
	 * @throws cs_InviteActiveSendLimitIsExceeded
	 * @throws cs_InviteIsDuplicated
	 * @throws cs_InviteStatusIsNotExpected
	 * @throws cs_PlatformNotFound
	 */
	public static function run(int $sender_user_id, Short $member, array $group_meta_row, string $platform, bool $is_async_send_invite_message = true):void {

		// если передали не человека - не выполняем добавление
		if (!Type_User_Main::isHuman($member->npc_type)) {
			throw new Domain_Invite_Exception_IsNotHuman("cant send invite to bot");
		}

		// получаем запись с инвайтом
		$invite_row = Type_Conversation_Invite::getByConversationMapAndUserId($sender_user_id, $member->user_id, $group_meta_row["conversation_map"]);

		// если нет диалога, создаем
		$single_meta_row         = Helper_Single::createIfNotExist($sender_user_id, $member->user_id, true, false);
		$single_conversation_map = $single_meta_row["conversation_map"];

		Helper_Conversations::checkIsAllowed($single_meta_row["conversation_map"], $single_meta_row, $sender_user_id);

		// проверяем существует ли запись, если нет - создаем
		if (!isset($invite_row["invite_map"])) {
			$invite_row = self::_create($sender_user_id, $member->user_id, $single_meta_row["conversation_map"], $group_meta_row);
		}

		if (!Type_Conversation_Meta_Users::isMember($member->user_id, $group_meta_row["users"])) {

			// если приглашение не имеет статус auto_accepted, то устанавливаем инвайту статус auto_accepted
			if ($invite_row["status"] != Type_Invite_Handler::STATUS_AUTO_ACCEPTED) {
				Type_Invite_Single::setAutoAccepted($invite_row, $member->user_id, $group_meta_row);
			}

			// добавляем в диалог
			self::_doJoinUserToConversation($group_meta_row["conversation_map"], $sender_user_id, $member);
		}

		// отправляем сообщения об инвайте
		if ($is_async_send_invite_message) {
			Type_Phphooker_Main::sendInviteToUser($sender_user_id, $member->user_id, $invite_row["invite_map"], $single_conversation_map, $group_meta_row, false);
		} else {
			self::_sendInviteToUser($sender_user_id, $invite_row["invite_map"], $single_conversation_map, $platform);
		}
	}

	/**
	 * создание инвайта
	 *
	 * @param int    $sender_user_id
	 * @param int    $user_id
	 * @param string $single_conversation_map
	 * @param array  $group_meta_row
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws cs_ErrorSocketRequest
	 * @throws cs_InviteActiveSendLimitIsExceeded
	 * @throws cs_InviteIsDuplicated
	 */
	protected static function _create(int $sender_user_id, int $user_id, string $single_conversation_map, array $group_meta_row):array {

		$created_at = time();
		$meta_id    = Type_Autoincrement_Main::getNextId(Type_Autoincrement_Main::INVITE);

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// пытаемся создать инвайт
		try {
			$invite_row = self::_createInviteAutoAccepted($meta_id, $created_at, $sender_user_id, $user_id, $single_conversation_map, $group_meta_row);
		} catch (cs_ErrorSocketRequest|ReturnFatalException|cs_InviteActiveSendLimitIsExceeded|cs_InviteIsDuplicated $e) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new $e();
		}

		Gateway_Db_CompanyConversation_Main::commitTransaction();
		return $invite_row;
	}

	// пытаемся создать инвайт, если возможно
	protected static function _createInviteAutoAccepted(int $meta_id, int $created_at, int $sender_user_id, int $user_id, string $single_conversation_map, array $group_meta_row):array {

		// получаем мету и формируем invite_row
		$shard_id = \CompassApp\Pack\Invite::getShardIdByTime($created_at);
		Gateway_Db_CompanyConversation_InviteList::insert($meta_id, SINGLE_INVITE_TO_GROUP, $created_at, $shard_id);
		$invite_map = \CompassApp\Pack\Invite::doPack($shard_id, $meta_id, SINGLE_INVITE_TO_GROUP);

		// создаем запись во всех базах
		$invite_row = Gateway_Db_CompanyConversation_InviteGroupViaSingle::create(
			$user_id, $sender_user_id, Type_Invite_Handler::STATUS_AUTO_ACCEPTED, $group_meta_row, $single_conversation_map, $invite_map, $created_at
		);

		Gateway_Db_CompanyConversation_ConversationInviteList::insert(
			$user_id,
			$sender_user_id,
			$invite_map,
			$created_at,
			$invite_row["status"],
			$group_meta_row["conversation_map"]
		);

		Gateway_Db_CompanyConversation_UserInviteRel::insert(
			$invite_row["user_id"],
			$invite_row["sender_user_id"],
			$invite_row["invite_map"],
			$invite_row["created_at"],
			$invite_row["status"],
			$invite_row["group_conversation_map"]
		);

		return $invite_row;
	}

	/**
	 * добавляем пользователя в диалог
	 *
	 * @param string $conversation_map
	 * @param int    $inviter_user_id
	 * @param Short  $invited_member
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	protected static function _doJoinUserToConversation(string $conversation_map, int $inviter_user_id, Short $invited_member):void {

		Helper_Groups::doJoin(
			$conversation_map,
			$invited_member->user_id,
			$invited_member->role,
			$invited_member->permissions,
			$inviter_user_id,
		);
	}

	/**
	 * отправляем инвайт пользователю
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \returnException
	 * @throws cs_PlatformNotFound
	 */
	protected static function _sendInviteToUser(int $inviter_user_id, string $invite_map, string $single_conversation_map, string $platform):void {

		$single_meta_row = Type_Conversation_Meta::get($single_conversation_map);

		// пользователи, для которых не отправляем ws-ивент
		$not_send_ws_event_user_list = [];

		// если у приглашающего отсутствует single-диалог с приглашенным в левом меню или он спрятан
		$left_menu_row = Type_Conversation_LeftMenu::get($inviter_user_id, $single_conversation_map);
		if (!isset($left_menu_row["user_id"]) || $left_menu_row["is_hidden"] == 1) {
			$not_send_ws_event_user_list = [$inviter_user_id];
		}

		try {
			Helper_Conversations::checkIsAllowed($single_conversation_map, $single_meta_row, $inviter_user_id);
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted|Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return;
		}

		self::_addMessageWithInvite($single_conversation_map, $inviter_user_id, $invite_map, $single_meta_row, $platform, $not_send_ws_event_user_list);
	}

	// добавляем сообщение с приглашением в single-диалог
	protected static function _addMessageWithInvite(string $single_conversation_map, int $sender_user_id, string $invite_map, array $single_meta_row, string $platform, array $not_send_ws_event_user_list):void {

		$invite_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeInvite($sender_user_id, $invite_map, $platform);

		self::_addMessageList(
			$single_conversation_map,
			[$invite_message],
			$single_meta_row["users"],
			$single_meta_row["type"],
			$single_meta_row["conversation_name"],
			$single_meta_row["extra"],
			$not_send_ws_event_user_list
		);
	}

	// добавляем и отправляем список сообщений в диалог
	protected static function _addMessageList(string $conversation_map, array $message_list, array $users, int $conversation_type, string $conversation_name, array $conversation_extra, array $not_send_ws_event_user_list = [], bool $is_silent = true):bool {

		try {

			Helper_Conversations::addMessageList(
				$conversation_map,
				$message_list,
				$users,
				$conversation_type,
				$conversation_name,
				$conversation_extra,
				true,
				$is_silent,
				$not_send_ws_event_user_list
			);
		} catch (cs_ConversationIsLocked) {
			return false;
		} catch (cs_Message_DuplicateClientMessageId $e) {

			if (ServerProvider::isMaster()) {
				Type_System_Admin::log("duplicate_message_test", [$conversation_map, $conversation_name, $conversation_type, $message_list]);
			}

			throw $e;
		}

		return true;
	}
}
