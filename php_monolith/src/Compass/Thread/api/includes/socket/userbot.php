<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * контроллер для сокет методов класса userbot
 */
class Socket_Userbot extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. регистр не имеет значение */
	public const ALLOW_METHODS = [
		"sendMessageToThread",
		"addReaction",
		"removeReaction",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод для отправки сообщения в тред от бота
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_Thread_UserNotMember
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function sendMessageToThread():array {

		$userbot_user_id = $this->post(\Formatter::TYPE_INT, "userbot_user_id");
		$message_key     = $this->post(\Formatter::TYPE_STRING, "message_key");
		try {
			$message_map = \CompassApp\Pack\Message::doDecrypt($message_key);
		} catch (\cs_DecryptHasFailed) {
			return $this->error(10014, "message_key is incorrect");
		}

		$text     = $this->post(\Formatter::TYPE_STRING, "text", false);
		$file_key = $this->post(\Formatter::TYPE_STRING, "file_key", false);

		if ($text !== false) {

			// валидируем и преобразуем текст сообщения
			$text = Type_Api_Filter::replaceEmojiWithShortName($text);
			if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
				return $this->error(10014, "text or file_key is incorrect");
			}

			$text = Type_Api_Filter::sanitizeMessageText($text);
			if (isEmptyString($text)) {
				return $this->error(10014, "text or file_key is incorrect");
			}
		}

		// отправляем сообщение в тред
		try {
			$message_key = Domain_Userbot_Scenario_Socket::sendMessageToThread($userbot_user_id, $message_map, $text, $file_key);
		} catch (\cs_DecryptHasFailed) {
			return $this->error(10014, "text or file_key is incorrect");
		} catch (cs_ThreadIsReadOnly|cs_ConversationIsLocked|cs_Conversation_IsBlockedOrDisabled|cs_Message_HaveNotAccess) {
			return $this->error(10015, "not allowed for userbot");
		} catch (cs_Message_IsDeleted) {
			return $this->error(10022, "message is not found");
		} catch (cs_Thread_ParentEntityNotFound) {
			return $this->error(10023, "conversation is not found");
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParseFatalException("passed empty message list");
		}

		return $this->ok([
			"message_key" => (string) $message_key,
		]);
	}

	/**
	 * метод для добавления реакции на сообщение
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function addReaction():array {

		$userbot_user_id = $this->post(\Formatter::TYPE_INT, "userbot_user_id");
		$message_key     = $this->post(\Formatter::TYPE_STRING, "message_key");
		try {
			$message_map = \CompassApp\Pack\Message::doDecrypt($message_key);
		} catch (\cs_DecryptHasFailed) {
			return $this->error(10016, "message_key is incorrect");
		}

		$reaction = $this->post(\Formatter::TYPE_STRING, "reaction");

		try {
			Domain_Userbot_Scenario_Socket::addReaction($userbot_user_id, $message_map, $reaction);
		} catch (cs_Conversation_IsBlockedOrDisabled|cs_Message_HaveNotAccess|cs_Thread_UserNotMember) {
			return $this->error(10018, "not allowed for userbot");
		} catch (cs_Message_IsNotAllowedForReaction) {
			return $this->error(10017, "reaction is not found");
		}

		return $this->ok();
	}

	/**
	 * метод для удаления реакции с сообщения
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function removeReaction():array {

		$userbot_user_id = $this->post(\Formatter::TYPE_INT, "userbot_user_id");
		$message_key     = $this->post(\Formatter::TYPE_STRING, "message_key");
		try {
			$message_map = \CompassApp\Pack\Message::doDecrypt($message_key);
		} catch (\cs_DecryptHasFailed) {
			return $this->error(10019, "message_key is incorrect");
		}

		$reaction = $this->post(\Formatter::TYPE_STRING, "reaction");

		try {

			Domain_Userbot_Scenario_Socket::removeReaction($userbot_user_id, $message_map, $reaction);
		} catch (cs_Conversation_IsBlockedOrDisabled|cs_Message_HaveNotAccess|cs_Thread_UserNotMember) {
			return $this->error(10021, "not allowed for userbot");
		} catch (cs_Message_IsNotAllowedForReaction) {
			return $this->error(10020, "reaction is not found");
		}

		return $this->ok();
	}
}