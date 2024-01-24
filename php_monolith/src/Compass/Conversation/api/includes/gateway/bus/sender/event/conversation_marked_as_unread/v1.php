<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_marked_as_unread версии 1
 */
class Gateway_Bus_Sender_Event_ConversationMarkedAsUnread_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_marked_as_unread";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map"           => \Entity_Validator_Structure::TYPE_STRING,
		"conversations_unread_count" => \Entity_Validator_Structure::TYPE_INT,
		"messages_unread_count"      => \Entity_Validator_Structure::TYPE_INT,
		"left_menu_version"          => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map, array $meta):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map"           => (string) $conversation_map,
			"conversations_unread_count" => (int) $meta["conversations_unread_count"],
			"messages_unread_count"      => (int) $meta["messages_unread_count"],
			"left_menu_version"          => (int) $meta["left_menu_version"],
		]);
	}
}