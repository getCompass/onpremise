<?php

namespace Compass\Conversation;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Действие для получения данных для пуша сообщения
 */
class Domain_Conversation_Action_Message_GetPushData {

	/**
	 * выполняем
	 * @long
	 */
	public static function do(array $message, int $conversation_type, string $conversation_name):array {

		$push_data = [];

		// если публичный диалог, то данные для пуша не нужны
		if (Type_Conversation_Meta::isSubtypeOfPublicGroup($conversation_type)) {
			return $push_data;
		}

		// если это сообщение-Напоминание
		if (Type_Conversation_Message_Main::getHandler($message)::isSystemBotRemind($message)) {
			return self::_getIfRemindMessage($message, $conversation_type, $conversation_name);
		}

		// получим информацию из кэша
		$full_name     = "";
		$user_npc_type = 0;

		if (Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message) > 0) {

			$user_info     = Gateway_Bus_CompanyCache::getMember(Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message));
			$full_name     = $user_info->full_name;
			$user_npc_type = $user_info->npc_type;
		}

		// получаем заголовок для пуша
		$push_title        = Type_Conversation_Message_Main::getHandler($message)::getPushTitle($message, $conversation_type, $conversation_name, $full_name);
		$push_title_locale = Type_Conversation_Message_Main::getHandler($message)::getPushTitleLocale($message, $user_npc_type);

		// получаем содержание текста для пуша
		$push_body        = Type_Conversation_Message_Main::getHandler($message)::getPushBody($message, $conversation_type, $full_name);
		$push_body_locale = Type_Conversation_Message_Main::getHandler($message)::getPushBodyLocale($message, $conversation_type, $full_name, $user_npc_type);

		$message_map = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
		return Gateway_Bus_Pusher::makeConversationMessagePushData(
			\CompassApp\Pack\Message\Conversation::getConversationMap($message_map),
			$message_map,
			$push_title,
			$push_body,
			$push_body_locale,
			Type_Conversation_Message_Main::getHandler($message)::getEventType($message),
			Type_Conversation_Utils::makeConversationMessagePushDataEventSubtype($conversation_type),
			Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message),
			$user_npc_type,
			$conversation_type,
			Type_Conversation_Message_Main::getHandler($message)::getType($message),
			push_title_locale: $push_title_locale,
		);
	}

	/**
	 * получаем данные пуша, если это Напоминание
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \returnException
	 *
	 * @long its so long ...
	 */
	#[ArrayShape(["badge_inc_count" => "int", "push_type" => "int", "event_type" => "int", "text_push" => "array"])]
	protected static function _getIfRemindMessage(array $message, int $conversation_type, string $conversation_name):array {

		// получаем бота-отправителя (Напоминание) и отправителя сообщения-оригинала
		$sender_user_id                   = Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message);
		$recipient_message_sender_user_id = Type_Conversation_Message_Main::getHandler($message)::getRemindRecipientMessageSenderId($message);

		// получаем по ним информацию из кэша
		$user_info_list = Gateway_Bus_CompanyCache::getMemberList([$sender_user_id, $recipient_message_sender_user_id]);

		$sender_info              = $user_info_list[$sender_user_id];                   // отправитель бот-Напоминание
		$recipient_message_sender = $user_info_list[$recipient_message_sender_user_id]; // информация об отправителе сообщения-оригинала

		// получаем заголовок для пуша
		$push_title        = Type_Conversation_Message_Main::getHandler($message)::getPushTitle($message, $conversation_type, $conversation_name, $sender_info->full_name);
		$push_title_locale = Type_Conversation_Message_Main::getHandler($message)::getPushTitleLocale($message, $sender_info->npc_type);

		// получаем содержание текста для пуша
		$push_body        = Type_Conversation_Message_Main::getHandler($message)::getPushBody($message, $conversation_type, $recipient_message_sender->full_name);
		$push_body_locale = Type_Conversation_Message_Main::getHandler($message)::getPushBodyLocale(
			$message, $conversation_type, $recipient_message_sender->full_name, $recipient_message_sender->npc_type
		);

		$message_map = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
		$user_type   = $sender_info->npc_type;

		return Gateway_Bus_Pusher::makeConversationMessagePushData(
			\CompassApp\Pack\Message\Conversation::getConversationMap($message_map),
			$message_map,
			$push_title,
			$push_body,
			$push_body_locale,
			Type_Conversation_Message_Main::getHandler($message)::getEventType($message),
			Type_Conversation_Utils::makeConversationMessagePushDataEventSubtype($conversation_type),
			Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message),
			$user_type,
			$conversation_type,
			Type_Conversation_Message_Main::getHandler($message)::getType($message),
			Type_Conversation_Message_Main::getHandler($message)::isNeedForcePush($message),
			push_title_locale: $push_title_locale,
		);
	}
}