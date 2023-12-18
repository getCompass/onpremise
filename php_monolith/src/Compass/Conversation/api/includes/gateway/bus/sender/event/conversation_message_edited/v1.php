<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие action.conversation_message_edited версии 1
 */
class Gateway_Bus_Sender_Event_ConversationMessageEdited_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.conversation_message_edited";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message_map"                 => \Entity_Validator_Structure::TYPE_STRING,
		"new_text"                    => \Entity_Validator_Structure::TYPE_STRING,
		"client_message_id"           => \Entity_Validator_Structure::TYPE_STRING,
		"conversation_map"            => \Entity_Validator_Structure::TYPE_STRING,
		"last_message_text_edited_at" => \Entity_Validator_Structure::TYPE_INT,
		"mention_user_id_list"        => \Entity_Validator_Structure::TYPE_ARRAY,
		"diff_mentioned_user_id_list" => \Entity_Validator_Structure::TYPE_ARRAY,
		"message"                     => \Entity_Validator_Structure::TYPE_OBJECT,
		"messages_updated_version"    => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @param string $message_map
	 * @param array  $message
	 * @param array  $prepared_message
	 * @param string $conversation_map
	 * @param array  $mention_user_id_list
	 * @param int    $messages_updated_version
	 * @param array  $diff_mentioned_user_id_list
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $message_map,
						   array $message,
						   array $prepared_message,
						   string $conversation_map,
						   array $mention_user_id_list,
						   int $messages_updated_version,
						   array $diff_mentioned_user_id_list):Struct_Sender_Event {

		return self::_buildEvent([
			"message_map"                 => (string) $message_map,
			"new_text"                    => (string) Type_Conversation_Message_Main::getHandler($message)::getText($message),
			"client_message_id"           => (string) Type_Conversation_Message_Main::getHandler($message)::getClientMessageId($message),
			"conversation_map"            => (string) $conversation_map,
			"last_message_text_edited_at" => (int) Type_Conversation_Message_Main::getHandler($message)::getLastMessageTextEditedAt($message),
			"mention_user_id_list"        => (array) arrayValuesInt($mention_user_id_list),
			"diff_mentioned_user_id_list" => (array) arrayValuesInt($diff_mentioned_user_id_list),
			"message"                     => (object) Apiv1_Format::conversationMessage($prepared_message),
			"messages_updated_version"    => (int) $messages_updated_version,
		]);
	}
}