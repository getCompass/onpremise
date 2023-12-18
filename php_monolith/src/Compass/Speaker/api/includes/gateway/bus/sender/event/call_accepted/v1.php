<?php

namespace Compass\Speaker;

/**
 * класс описывающий событие event.call_accepted версии 1
 */
class Gateway_Bus_Sender_Event_CallAccepted_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.call_accepted";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"call_map" => \Entity_Validator_Structure::TYPE_STRING,
		"user_id"  => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $call_map, int $user_id):Struct_Sender_Event {

		return self::_buildEvent([
			"call_map" => (string) $call_map,
			"user_id"  => (int) $user_id,
		]);
	}
}