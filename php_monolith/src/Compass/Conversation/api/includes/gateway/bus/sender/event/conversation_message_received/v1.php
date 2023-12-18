<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие action.conversation_message_received версии 1
 */
class Gateway_Bus_Sender_Event_ConversationMessageReceived_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.conversation_message_received";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message"                  => \Entity_Validator_Structure::TYPE_OBJECT,
		"messages_updated_version" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $message, int $messages_updated_version):Struct_Sender_Event {

		return self::_buildEvent([
			"message"                  => (object) $message,
			"messages_updated_version" => (int) $messages_updated_version,
		]);
	}
}