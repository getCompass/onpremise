<?php

namespace Compass\Thread;

/**
 * класс описывающий событие action.conversation_message_thread_attached версии 1
 */
class Gateway_Bus_Sender_Event_ConversationMessageThreadAttached_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.conversation_message_thread_attached";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"thread_meta"      => \Entity_Validator_Structure::TYPE_OBJECT,
		"message_map"      => \Entity_Validator_Structure::TYPE_STRING,
		"thread_map"       => \Entity_Validator_Structure::TYPE_STRING,
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $thread_meta, string $message_map, string $thread_map, string $conversation_map):Struct_Sender_Event {

		return self::_buildEvent([
			"thread_meta"      => (object) $thread_meta,
			"message_map"      => (string) $message_map,
			"thread_map"       => (string) $thread_map,
			"conversation_map" => (string) $conversation_map,
		]);
	}
}