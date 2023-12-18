<?php

namespace Compass\Speaker;

/**
 * класс описывающий событие event.call_speak_started версии 1
 */
class Gateway_Bus_Sender_Event_CallSpeakStarted_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.call_speak_started";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"call"     => \Entity_Validator_Structure::TYPE_OBJECT,
		"call_map" => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $formatted_call, string $call_map):Struct_Sender_Event {

		return self::_buildEvent([
			"call"     => (object) $formatted_call,
			"call_map" => (string) $call_map,
		]);
	}
}