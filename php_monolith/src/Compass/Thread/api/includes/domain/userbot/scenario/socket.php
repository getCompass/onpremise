<?php declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс обработки сценариев сокетов.
 */
class Domain_Userbot_Scenario_Socket {

	/**
	 * отправляем сообщение в тред от бота
	 *
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_ThreadIsReadOnly
	 * @throws cs_Thread_UserNotMember
	 */
	public static function sendMessageToThread(int $userbot_user_id, string $message_map, string|false $text, string|false $file_key):string {

		// создаём тред у сообщения, если необходимо
		try {
			$thread_meta_row = Domain_Thread_Action_AddToConversationMessage::do($userbot_user_id, $message_map);
		} catch (\Exception | \Error) {

			// если не смогли получить тред, значит нет доступа
			throw new cs_Message_HaveNotAccess();
		}

		$thread_map = $thread_meta_row["thread_map"];

		// пользователь является участником существующего треда
		$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $userbot_user_id);

		$mention_user_id_list = [];
		if ($file_key !== false) {

			$file_map = \CompassApp\Pack\File::tryDecrypt($file_key);
			$message  = Type_Thread_Message_Main::getLastVersionHandler()::makeFile($userbot_user_id, "", generateUUID(), $file_map);
		} else {

			$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($meta_row, $text);
			$message              = Type_Thread_Message_Main::getLastVersionHandler()::makeText($userbot_user_id, $text, generateUUID(), $mention_user_id_list);
		}

		$message = Type_Thread_Message_Main::getHandler($message)::setUserbotSender($message);
		$message = Type_Thread_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

		$data = Domain_Thread_Action_Message_AddList::do($thread_map, $meta_row, [$message], $mention_user_id_list);
		[$message] = $data["message_list"];

		return \CompassApp\Pack\Message::doEncrypt($message["message_map"]);
	}

	/**
	 * добавляем реакцию на сообщение
	 *
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsNotAllowedForReaction
	 * @throws cs_Thread_UserNotMember
	 */
	public static function addReaction(int $userbot_user_id, string $message_map, string $reaction):void {

		if (!\CompassApp\Pack\Message::isFromThread($message_map)) {
			throw new ParamException("The message is not from thread");
		}

		$reaction = Type_Api_Filter::replaceEmojiWithShortName($reaction);

		$reaction_name = Type_Thread_Reaction_Main::getReactionNameIfExist($reaction);
		if (mb_strlen($reaction_name) < 1) {
			throw new cs_Message_IsNotAllowedForReaction(__CLASS__ . ": reaction does not exist");
		}

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		$meta_row   = Helper_Threads::getMetaIfUserMember($thread_map, $userbot_user_id, false);
		Domain_Thread_Action_Message_AddReaction::do($message_map, $meta_row["thread_map"], $meta_row, $reaction_name, $userbot_user_id);
	}

	/**
	 * удаляем реакция с сообщения
	 *
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsNotAllowedForReaction
	 * @throws cs_Thread_UserNotMember
	 * @throws ParamException
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 */
	public static function removeReaction(int $userbot_user_id, string $message_map, string $reaction):void {

		if (!\CompassApp\Pack\Message::isFromThread($message_map)) {
			throw new ParamException("the message is not from thread");
		}

		$reaction = Type_Api_Filter::replaceEmojiWithShortName($reaction);

		$reaction_name = Type_Thread_Reaction_Main::getReactionNameIfExist($reaction);
		if (mb_strlen($reaction_name) < 1) {
			throw new cs_Message_IsNotAllowedForReaction(__CLASS__ . ": reaction does not exist");
		}

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		$meta_row   = Helper_Threads::getMetaIfUserMember($thread_map, $userbot_user_id, false);

		Domain_Thread_Action_Message_RemoveReaction::do($message_map, $meta_row["thread_map"], $reaction_name, $userbot_user_id, $meta_row["users"]);
	}
}