<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_is_muted_changed версии 1
 */
class Gateway_Bus_Sender_Event_ConversationIsMutedChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_is_muted_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
		"is_muted"         => \Entity_Validator_Structure::TYPE_BOOL,
		"muted_until"      => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map, int $is_muted, int $muted_until):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map" => (string) $conversation_map,
			"is_muted"         => (int) $is_muted,
			"muted_until"      => (int) $muted_until,
		]);
	}
}