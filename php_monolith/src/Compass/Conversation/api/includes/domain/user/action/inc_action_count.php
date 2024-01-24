<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use CompassApp\Domain\Member\Struct\Short;

/**
 * Действие для инкремента количества действий
 */
class Domain_User_Action_IncActionCount {

	protected const _GROUPS_CREATED                = "groups_created";
	protected const _CONVERSATIONS_READ            = "conversations_read";
	protected const _CONVERSATION_MESSAGES_SENT    = "conversation_messages_sent";
	protected const _CONVERSATION_REACTIONS_ADDED  = "conversation_reactions_added";
	protected const _CONVERSATIONS_REMINDS_CREATED = "conversation_reminds_created";

	/**
	 * Инкрементим количество созданных групп
	 *
	 * @param int        $user_id
	 * @param string     $conversation_map
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function incGroupCreated(int $user_id, string $conversation_map, Short $user_info = null):void {

		self::_send($user_id, $conversation_map, self::_GROUPS_CREATED, $user_info);
	}

	/**
	 * Инкрементим прочтение диалога
	 *
	 * @param int        $user_id
	 * @param string     $conversation_map
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function incConversationRead(int $user_id, string $conversation_map, Short $user_info = null):void {

		self::_send($user_id, $conversation_map, self::_CONVERSATIONS_READ, $user_info);
	}

	/**
	 * Инкрементим количество отправленных сообщений
	 *
	 * @param string     $conversation_map
	 * @param array      $message_list
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function incMessageSent(string $conversation_map, array $message_list, Short $user_info = null):void {

		if (count($message_list) < 1) {
			return;
		}

		// если по этому типу сообщения не пишем стату
		$last_message = $message_list[count($message_list) - 1];
		$message_type = Type_Conversation_Message_Main::getHandler($last_message)::getType($last_message);
		if (in_array($message_type, [
			CONVERSATION_MESSAGE_TYPE_INVITE,
			CONVERSATION_MESSAGE_TYPE_SYSTEM,
			CONVERSATION_MESSAGE_TYPE_DELETED,
			CONVERSATION_MESSAGE_TYPE_CALL,
			CONVERSATION_MESSAGE_TYPE_RESPECT,
			CONVERSATION_MESSAGE_TYPE_SHARED_WIKI_PAGE,
			CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST,
			CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST,
			CONVERSATION_MESSAGE_TYPE_INVITE_TO_COMPANY_INVITER_SINGLE,
		])) {
			return;
		}

		$sender_user_id = Type_Conversation_Message_Main::getHandler($last_message)::getSenderUserId($last_message);
		self::_send($sender_user_id, $conversation_map, self::_CONVERSATION_MESSAGES_SENT, $user_info);
	}

	/**
	 * Инкрементим установку реакции
	 *
	 * @param int        $user_id
	 * @param string     $conversation_map
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function incConversationReactionAdded(int $user_id, string $conversation_map, Short $user_info = null):void {

		self::_send($user_id, $conversation_map, self::_CONVERSATION_REACTIONS_ADDED, $user_info);
	}

	/**
	 * Инкрементим установку напоминаний
	 *
	 * @param int        $user_id
	 * @param string     $conversation_map
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function incConversationRemindCreated(int $user_id, string $conversation_map, Short $user_info = null):void {

		self::_send($user_id, $conversation_map, self::_CONVERSATIONS_REMINDS_CREATED, $user_info);
	}

	/**
	 * Отправляем
	 *
	 * @param int        $user_id
	 * @param string     $conversation_map
	 * @param string     $action
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	protected static function _send(int $user_id, string $conversation_map, string $action, Short $user_info = null):void {

		if ($user_id < 1) {
			return;
		}

		// если не передали - получаем
		if (is_null($user_info)) {

			$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id], false);
			$user_info      = $user_info_list[$user_id];
		}

		// инкрементим количество действий
		$is_human = Type_User_Main::isHuman($user_info->npc_type);
		Gateway_Bus_Rating_Main::incActionCount($user_id, $conversation_map, $action, $is_human);
	}
}