<?php

namespace Compass\Conversation;

/**
 * контроллер для сокет методов класса userbot
 */
class Socket_Userbot extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. регистр не имеет значение */
	public const ALLOW_METHODS = [
		"sendMessageToUser",
		"sendMessageToGroup",
		"addReaction",
		"removeReaction",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод для отправки сообщения пользователю от бота
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function sendMessageToUser():array {

		$user_id         = $this->post(\Formatter::TYPE_INT, "user_id");
		$userbot_user_id = $this->post(\Formatter::TYPE_INT, "userbot_user_id");
		$text            = $this->post(\Formatter::TYPE_STRING, "text", false);
		$file_key        = $this->post(\Formatter::TYPE_STRING, "file_key", false);

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);

		if (!isset($user_info_list[$user_id])) {
			return $this->error(10001, "member is not found");
		}

		$user_info = $user_info_list[$user_id];
		if (\CompassApp\Domain\Member\Entity\Member::isDisabledProfile($user_info->role)) {
			return $this->error(10002, "member is kicked");
		}

		if ($text !== false) {

			// валидируем и преобразуем текст сообщения
			$text = Type_Api_Filter::replaceEmojiWithShortName($text);
			if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
				return $this->error(10000, "text or file_key is incorrect");
			}

			$text = Type_Api_Filter::sanitizeMessageText($text);
			if (isEmptyString($text)) {
				return $this->error(10000, "text or file_key is incorrect");
			}
		}

		// отправляем сообщение пользователю
		try {
			$message_key = Domain_Userbot_Scenario_Socket::sendMessageToUser($userbot_user_id, $user_id, $text, $file_key);
		} catch (\cs_DecryptHasFailed) {
			return $this->error(10000, "text or file_key is incorrect");
		} catch (cs_Conversation_IsNotAllowedForNewMessage) {
			return $this->error(10002, "member is kicked");
		}

		return $this->ok([
			"message_key" => (string) $message_key,
		]);
	}

	/**
	 * метод для отправки сообщения в группу от бота
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function sendMessageToGroup():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$userbot_user_id  = $this->post(\Formatter::TYPE_INT, "userbot_user_id");

		try {
			$conversation_map = \CompassApp\Pack\Conversation::doDecrypt($conversation_key);
		} catch (\cs_DecryptHasFailed) {
			return $this->error(10003, "incorrect conversation_key");
		}

		$text     = $this->post(\Formatter::TYPE_STRING, "text", false);
		$file_key = $this->post(\Formatter::TYPE_STRING, "file_key", false);

		if ($text !== false) {

			// валидируем и преобразуем текст сообщения
			$text = Type_Api_Filter::replaceEmojiWithShortName($text);
			if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
				return $this->error(10003, "text or file_key is incorrect");
			}

			$text = Type_Api_Filter::sanitizeMessageText($text);
			if (isEmptyString($text)) {
				return $this->error(10003, "text or file_key is incorrect");
			}
		}

		// отправляем сообщение в группу
		try {
			$message_key = Domain_Userbot_Scenario_Socket::sendMessageToGroup($userbot_user_id, $conversation_map, $text, $file_key);
		} catch (\cs_DecryptHasFailed) {
			return $this->error(10003, "text, file_key or conversation_key is incorrect");
		} catch (cs_UserIsNotMember) {
			return $this->error(10005, "userbot is not member of group");
		} catch (Domain_Conversation_Exception_NotGroup) {
			return $this->error(10004, "conversation is not group");
		} catch (Domain_Conversation_Exception_NotFound) {
			return $this->error(10023, "conversation not found");
		}

		return $this->ok([
			"message_key" => (string) $message_key,
		]);
	}

	/**
	 * метод для добавления реакции на сообщение
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function addReaction():array {

		$userbot_user_id = $this->post(\Formatter::TYPE_INT, "userbot_user_id");
		$message_key     = $this->post(\Formatter::TYPE_STRING, "message_key");
		try {
			$message_map = \CompassApp\Pack\Message::doDecrypt($message_key);
		} catch (\cs_DecryptHasFailed) {
			return $this->error(10006, "message_key is incorrect");
		}
		$reaction = $this->post(\Formatter::TYPE_STRING, "reaction");

		try {
			Domain_Userbot_Scenario_Socket::addReaction($userbot_user_id, $message_map, $reaction);
		} catch (cs_Message_IsDeleted|cs_UserIsNotMember|cs_Message_IsNotExist|
		cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDeleted|cs_Conversation_UserbotIsDisabled) {

			return $this->error(10008, "not allowed for userbot");
		} catch (\cs_DecryptHasFailed|\cs_UnpackHasFailed) {
			return $this->error(10006, "reaction or message_key is incorrect");
		} catch (cs_Message_IsNotAllowedForReaction) {
			return $this->error(10007, "reaction is not found");
		}

		return $this->ok();
	}

	/**
	 * метод для удаления реакции с сообщения
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function removeReaction():array {

		$userbot_user_id = $this->post(\Formatter::TYPE_INT, "userbot_user_id");
		$message_key     = $this->post(\Formatter::TYPE_STRING, "message_key");
		try {
			$message_map = \CompassApp\Pack\Message::doDecrypt($message_key);
		} catch (\cs_DecryptHasFailed) {
			return $this->error(10009, "message_key is incorrect");
		}

		$reaction = $this->post(\Formatter::TYPE_STRING, "reaction");

		try {
			Domain_Userbot_Scenario_Socket::removeReaction($userbot_user_id, $message_map, $reaction);
		} catch (cs_Message_IsDeleted|cs_UserIsNotMember|cs_Message_IsNotExist|
		cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted $e) {

			return $this->error(10011, "not allowed for userbot");
		} catch (\cs_DecryptHasFailed|\cs_UnpackHasFailed) {
			return $this->error(10009, "reaction or message_key is incorrect");
		} catch (cs_Message_IsNotAllowedForReaction) {
			return $this->error(10010, "reaction is not found");
		}

		return $this->ok();
	}
}