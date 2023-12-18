<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс обработки сценариев сокетов.
 */
class Domain_Userbot_Scenario_Socket {

	/**
	 * отправляем сообщение пользователю от бота
	 *
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Conversation_IsNotAllowedForNewMessage
	 * @long
	 */
	public static function sendMessageToUser(int $userbot_user_id, int $opponent_user_id, string|false $text, string|false $file_key):string {

		// валидируем user_id
		if ($opponent_user_id < 1) {
			throw new ParamException(__METHOD__ . ": malformed user_id");
		}

		// проверяем на создание диалога с самим собой
		if ($opponent_user_id == $userbot_user_id) {
			throw new ParamException("create single with yourself");
		}

		// создаем диалог
		$meta_row = Helper_Single::createIfNotExist($userbot_user_id, $opponent_user_id, false, false, true);

		try {
			Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $userbot_user_id);
		} catch (cs_Conversation_MemberIsDisabled) {
			throw new cs_Conversation_IsNotAllowedForNewMessage("You can't write to this conversation because your opponent is blocked in our system", 532);
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted) {
			throw new cs_Conversation_IsNotAllowedForNewMessage("You can't write to this conversation because your opponent delete account", 2118001);
		} catch (cs_Conversation_UserbotIsDisabled) {
			throw new cs_Conversation_IsNotAllowedForNewMessage("You can't write to this conversation because userbot is disabled", 2134001);
		} catch (cs_Conversation_UserbotIsDeleted) {
			throw new cs_Conversation_IsNotAllowedForNewMessage("You can't write to this conversation because userbot is deleted", 2134002);
		}

		$mention_user_id_list = [];

		// готовим сообщуху
		if ($file_key !== false) {

			$file_map = \CompassApp\Pack\File::tryDecrypt($file_key);

			$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeFile($userbot_user_id, "", generateUUID(), $file_map, "");
		} else {

			$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);
			$message              = Type_Conversation_Message_Main::getLastVersionHandler()::makeText($userbot_user_id, $text, generateUUID());
		}

		$message = Type_Conversation_Message_Main::getHandler($message)::setUserbotSender($message);
		$message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

		// отправляем сообщение
		[$message] = Helper_Conversations::addMessageList(
			$meta_row["conversation_map"],
			[$message],
			$meta_row["users"],
			(int) $meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"]
		);

		return \CompassApp\Pack\Message::doEncrypt($message["message_map"]);
	}

	/**
	 * отправляем сообщение в группу от бота
	 *
	 * @throws Domain_Conversation_Exception_NotFound
	 * @throws Domain_Conversation_Exception_NotGroup
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_UserIsNotMember
	 * @long
	 */
	public static function sendMessageToGroup(int $userbot_user_id, string $conversation_map, string|false $text, string|false $file_key):string {

		// получаем информацию о диалоге
		try {
			$meta_row = Type_Conversation_Meta::get($conversation_map);
		} catch (ParamException) {
			throw new Domain_Conversation_Exception_NotFound("conversation not found");
		}
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::ADD_MESSAGE_FROM_CONVERSATION);

		// проверяем, что пользователь его участник
		if (!Type_Conversation_Meta_Users::isMember($userbot_user_id, $meta_row["users"])) {
			throw new cs_UserIsNotMember("User is not conversation member");
		}

		// проверяем, что диалог групповой
		if (!Type_Conversation_Meta::isSubtypeOfGroup((int) $meta_row["type"])) {
			throw new Domain_Conversation_Exception_NotGroup("conversation must be group in " . __METHOD__);
		}

		$mention_user_id_list = [];

		// готовим сообщуху
		if ($file_key !== false) {

			$file_map = \CompassApp\Pack\File::tryDecrypt($file_key);

			$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeFile($userbot_user_id, "", generateUUID(), $file_map, "");
		} else {

			$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);
			$message              = Type_Conversation_Message_Main::getLastVersionHandler()::makeText($userbot_user_id, $text, generateUUID());
		}

		$message = Type_Conversation_Message_Main::getHandler($message)::setUserbotSender($message);
		$message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

		// отправляем сообщение
		[$message] = Helper_Conversations::addMessageList(
			$meta_row["conversation_map"],
			[$message],
			$meta_row["users"],
			(int) $meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"]
		);

		return \CompassApp\Pack\Message::doEncrypt($message["message_map"]);
	}

	/**
	 * добавляем реакцию на сообщение
	 *
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 * @throws cs_Message_IsNotAllowedForReaction
	 * @throws cs_UserIsNotMember
	 */
	public static function addReaction(int $userbot_user_id, string $message_map, string $reaction):void {

		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("The message is not from conversation");
		}

		$reaction = Type_Api_Filter::replaceEmojiWithShortName($reaction);

		$reaction_name = Type_Conversation_Reaction_Main::getReactionNameIfExist($reaction);
		if (mb_strlen($reaction_name) < 1) {
			throw new cs_Message_IsNotAllowedForReaction(__CLASS__ . ": reaction does not exist");
		}

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::REACTION_TO_MESSAGE_FROM_CONVERSATION);

		// если пользователь не является участником группы
		Type_Conversation_Meta_Users::assertIsMember($userbot_user_id, $meta_row["users"]);

		Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $userbot_user_id);
		Domain_Conversation_Action_Message_AddReaction::do($message_map, $meta_row["conversation_map"], $meta_row, $reaction_name, $userbot_user_id);
	}

	/**
	 * удаляем реакция с сообщения
	 *
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 * @throws cs_Message_IsNotAllowedForReaction
	 * @throws cs_UserIsNotMember
	 */
	public static function removeReaction(int $userbot_user_id, string $message_map, string $reaction):void {

		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("The message is not from conversation");
		}

		$reaction      = Type_Api_Filter::replaceEmojiWithShortName($reaction);
		$reaction_name = Type_Conversation_Reaction_Main::getReactionNameIfExist($reaction);
		if (mb_strlen($reaction_name) < 1) {
			throw new cs_Message_IsNotAllowedForReaction(__CLASS__ . ": reaction does not exist");
		}

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::REACTION_TO_MESSAGE_FROM_CONVERSATION);
		Type_Conversation_Meta_Users::assertIsMember($userbot_user_id, $meta_row["users"]);

		Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $userbot_user_id);
		Domain_Conversation_Action_Message_RemoveReaction::do($message_map, $meta_row["conversation_map"], $reaction_name, $userbot_user_id, $meta_row["users"]);
	}
}