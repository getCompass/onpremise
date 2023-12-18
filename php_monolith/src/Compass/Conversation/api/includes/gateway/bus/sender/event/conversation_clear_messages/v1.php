<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_clear_messages версии 1
 */
class Gateway_Bus_Sender_Event_ConversationClearMessages_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_clear_messages";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map"         => \Entity_Validator_Structure::TYPE_STRING,
		"messages_updated_version" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map, int $messages_updated_version):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map"         => (string) $conversation_map,
			"messages_updated_version" => (int) $messages_updated_version,
		]);
	}
}