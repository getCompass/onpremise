<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие action.conversation_message_read версии 1
 */
class Gateway_Bus_Sender_Event_ConversationMessageRead_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_message_read";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message_map"      => \Entity_Validator_Structure::TYPE_STRING,
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public static function makeEvent(array $prepared_message):Struct_Sender_Event {

		return self::_buildEvent([
			"message_map"      => (string) $prepared_message["message_map"],
			"conversation_map" => (string) \CompassApp\Pack\Message\Conversation::getConversationMap($prepared_message["message_map"]),
		]);
	}
}