<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие action.conversation_message_list_deleted версии 1
 */
class Gateway_Bus_Sender_Event_ConversationMessageListDeleted_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.conversation_message_list_deleted";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message_map_list"         => \Entity_Validator_Structure::TYPE_ARRAY,
		"conversation_map"         => \Entity_Validator_Structure::TYPE_STRING,
		"messages_updated_version" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $message_map_list, string $conversation_map, int $messages_updated_version):Struct_Sender_Event {

		return self::_buildEvent([
			"message_map_list"         => (array) $message_map_list,
			"conversation_map"         => (string) $conversation_map,
			"messages_updated_version" => (int) $messages_updated_version,
		]);
	}
}