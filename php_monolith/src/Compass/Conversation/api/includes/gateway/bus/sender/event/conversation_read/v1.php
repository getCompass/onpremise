<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_read версии 1
 */
class Gateway_Bus_Sender_Event_ConversationRead_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_read";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map"           => \Entity_Validator_Structure::TYPE_STRING,
		"last_read"                  => [
			"?message_map" => \Entity_Validator_Structure::TYPE_STRING,
		],
		"left_menu"                  => \Entity_Validator_Structure::TYPE_OBJECT,
		"messages_unread_count"      => \Entity_Validator_Structure::TYPE_INT,
		"conversations_unread_count" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $message_map, array $prepared_left_menu_row, array $meta):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map"           => (string) $prepared_left_menu_row["conversation_map"],
			"last_read"                  => (mb_strlen($message_map) < 1) ? (object) [] : (object) [
				"message_map" => (string) $message_map,
			],
			"left_menu"                  => (object) $prepared_left_menu_row,
			"messages_unread_count"      => (int) $meta["messages_unread_count"],
			"conversations_unread_count" => (int) $meta["conversations_unread_count"],
		]);
	}
}