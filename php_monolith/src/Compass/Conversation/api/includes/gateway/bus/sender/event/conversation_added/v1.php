<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_added версии 1
 */
class Gateway_Bus_Sender_Event_ConversationAdded_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_added";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map" => (string) $conversation_map,
		]);
	}
}