<?php

namespace Compass\Userbot;

/**
 * Действие для отправки ответа со стороны внешнего сервиса
 *
 * Class Domain_Userbot_Action_SendWebhookAnswer
 */
class Domain_Userbot_Action_SendWebhookAnswer {

	protected const _MESSAGE_SEND         = "message_send";        // отправляем сообщение на команду боту
	protected const _THREAD_SEND          = "thread_send";         // отправляем сообщение в тред к команде
	protected const _MESSAGE_ADD_REACTION = "message_addreaction"; // добавляем реакцию на сообщение-команду

	/**
	 * выполняем
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_Conversation_IsNotAllowed
	 * @throws \cs_Conversation_IsNotFound
	 * @throws \cs_Conversation_IsNotGroup
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_Member_IsKicked
	 * @throws \cs_Member_IsNotFound
	 * @throws \cs_Message_IsNotAllowed
	 * @throws \cs_Message_IsNotFound
	 * @throws \cs_Reaction_IsNotFound
	 * @throws \cs_Userbot_NotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function do(string $token, array $answer, string $group_id, string $message_id, int $user_id):void {

		if (!isset($answer["action"], $answer["post"])) {
			return;
		}

		$userbot = Gateway_Bus_UserbotCache::get($token);

		switch (mb_strtolower($answer["action"])) {

			case self::_MESSAGE_SEND:
				self::_messageSend($userbot, $group_id, $user_id, $answer["post"]);
				break;

			case self::_THREAD_SEND:
				self::_threadSend($userbot, $message_id, $answer["post"]);
				break;

			case self::_MESSAGE_ADD_REACTION:
				self::_messageAddReaction($userbot, $message_id, $answer["post"]);
				break;

			default:
				break;
		}
	}

	/**
	 * отправляем сообщение в группу/лс с пользователем
	 *
	 * @throws \cs_Conversation_IsNotAllowed
	 * @throws \cs_Conversation_IsNotFound
	 * @throws \cs_Conversation_IsNotGroup
	 * @throws \cs_Member_IsKicked
	 * @throws \cs_Member_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _messageSend(Struct_Userbot_Info $userbot, string $group_id, int $user_id, array $answer_post):void {

		$type    = $answer_post["type"] ?? "";
		$text    = $answer_post["text"] ?? false;
		$file_id = $answer_post["file_id"] ?? false;

		try {
			Domain_Userbot_Action_CheckParamsForSendMessage::do($type, $text, $file_id);
		} catch (\cs_Userbot_RequestIncorrect) {
			return;
		}

		if (mb_strlen($group_id) > 0) {
			Gateway_Socket_Conversation::sendMessageToGroup($userbot->userbot_user_id, $group_id, $text, $file_id, $userbot->domino_entrypoint, $userbot->company_id);
		} else {
			Gateway_Socket_Conversation::sendMessageToUser($userbot->userbot_user_id, $user_id, $text, $file_id, $userbot->domino_entrypoint, $userbot->company_id);
		}
	}

	/**
	 * отправляем сообщение в тред
	 *
	 * @throws \cs_Message_IsNotAllowed
	 * @throws \cs_Message_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _threadSend(Struct_Userbot_Info $userbot, string $message_id, array $answer_post):void {

		$type    = $answer_post["type"] ?? "";
		$text    = $answer_post["text"] ?? false;
		$file_id = $answer_post["file_id"] ?? false;

		try {
			Domain_Userbot_Action_CheckParamsForSendMessage::do($type, $text, $file_id);
		} catch (\cs_Userbot_RequestIncorrect) {
			return;
		}

		Gateway_Socket_Thread::sendMessageToThread($userbot->userbot_user_id, $message_id, $text, $file_id, $userbot->domino_entrypoint, $userbot->company_id);
	}

	/**
	 * устанавливаем реакцию на сообщение
	 *
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_Message_IsNotAllowed
	 * @throws \cs_Reaction_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _messageAddReaction(Struct_Userbot_Info $userbot, string $message_id, array $answer_post):void {

		$reaction = $answer_post["reaction"] ?? false;

		if ($reaction === false || isEmptyString($reaction)) {
			return;
		}

		Domain_Message_Action_AddReaction::do($userbot->userbot_user_id, $message_id, $reaction, $userbot->domino_entrypoint, $userbot->company_id);
	}
}