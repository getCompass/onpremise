<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use CompassApp\Domain\Member\Struct\Short;

/**
 * Действие для инкремента количества действий
 */
class Domain_User_Action_IncActionCount {

	protected const _THREADS_CREATED        = "threads_created";
	protected const _THREADS_READ           = "threads_read";
	protected const _THREAD_MESSAGES_SENT   = "thread_messages_sent";
	protected const _THREAD_REACTIONS_ADDED = "thread_reactions_added";
	protected const _THREAD_REMINDS_CREATED = "thread_reminds_created";

	/**
	 * Инкрементим количество созданных тредов
	 *
	 * @param int        $user_id
	 * @param string     $conversation_map
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function incThreadCreated(int $user_id, string $conversation_map, Short $user_info = null):void {

		self::_send($user_id, $conversation_map, self::_THREADS_CREATED, $user_info);
	}

	/**
	 * Инкрементим прочтение треда
	 *
	 * @param int        $user_id
	 * @param string     $conversation_map
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function incThreadRead(int $user_id, string $conversation_map, Short $user_info = null):void {

		self::_send($user_id, $conversation_map, self::_THREADS_READ, $user_info);
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
		$message_type = Type_Thread_Message_Main::getHandler($last_message)::getType($last_message);
		if (in_array($message_type, [
			THREAD_MESSAGE_TYPE_DELETED,
			THREAD_MESSAGE_TYPE_SYSTEM,
			THREAD_MESSAGE_TYPE_CONVERSATION_CALL,
		])) {
			return;
		}

		$sender_user_id = Type_Thread_Message_Main::getHandler($last_message)::getSenderUserId($last_message);
		self::_send($sender_user_id, $conversation_map, self::_THREAD_MESSAGES_SENT, $user_info);
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
	public static function incThreadReactionAdded(int $user_id, string $conversation_map, Short $user_info = null):void {

		self::_send($user_id, $conversation_map, self::_THREAD_REACTIONS_ADDED, $user_info);
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
	public static function incThreadRemindCreated(int $user_id, string $conversation_map, Short $user_info = null):void {

		self::_send($user_id, $conversation_map, self::_THREAD_REMINDS_CREATED, $user_info);
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