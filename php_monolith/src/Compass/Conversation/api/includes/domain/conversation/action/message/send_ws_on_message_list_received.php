<?php

namespace Compass\Conversation;

/**
 * отправляем ws о добавлении списка сообщений в диалог
 */
class Domain_Conversation_Action_Message_SendWsOnMessageListReceived {

	/**
	 * выполняем
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(array $message_list, int $conversation_type, int $messages_updated_version, array $talking_user_list):void {

		// если диалог Найма, то отправка ws-события не нужна
		if (Type_Conversation_Meta::isHiringConversation($conversation_type)) {
			return;
		}

		$ws_users               = [];
		$formatted_message_list = [];
		foreach ($message_list as $message) {

			$formatted_message_list[] = (object) Apiv1_Format::conversationMessage(
				Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy($message)
			);

			$ws_users = array_merge($ws_users, Type_Conversation_Message_Main::getHandler($message)::getUsers($message));
		}

		Gateway_Bus_Sender::conversationMessageListReceived(
			$talking_user_list, $formatted_message_list, $messages_updated_version, [], array_unique($ws_users)
		);
	}
}