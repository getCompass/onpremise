<?php

namespace Compass\Conversation;

/**
 * класс, описывающий категорию событий типа message
 *
 * Class Type_SystemEvent_Message
 */
class Type_SystemEvent_Message extends Type_SystemEvent_Default {

	// структура событий
	protected const _EVENT_STRUCTURE = [

		// системный бот хочет отправить сообщение пользовтелю
		"send_system_message_list_to_user"               => [
			"version"  => 1,
			"required" => ["bot_user_id", "receiver_user_id", "message_list"],
			"default"  => [
				"important" => 0,
				"bot_type"  => "",
			],
		],

		// системный бот хочет отправить сообщение пользовтелю
		"send_system_message_list_to_conversation"       => [
			"version"  => 1,
			"required" => ["bot_user_id", "conversation_map", "message_list"],
			"default"  => [
				"important" => 0,
				"bot_type"  => "",
			],
		],

		// системный бот хочет отправить привекствие всем пользователям
		"force_welcome_system_message_list_to_user_list" => [
			"version"  => 1,
			"required" => ["bot_user_id", "receiver_user_id_list", "message_list"],
			"default"  => [
				"important" => 0,
				"bot_type"  => "",
			],
		],

		// системный бот хочет отправить сообщение с рейтингом пользователю
		"send_system_rating_message_to_user"             => [
			"version"  => 1,
			"required" => ["bot_user_id", "receiver_user_id", "year", "week", "count"],
			"default"  => [
				"important" => 0,
				"bot_type"  => "",
			],
		],
	];
}
