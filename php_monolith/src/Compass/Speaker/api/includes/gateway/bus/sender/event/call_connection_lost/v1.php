<?php

namespace Compass\Speaker;

/**
 * класс описывающий событие event.call_connection_lost версии 1
 */
class Gateway_Bus_Sender_Event_CallConnectionLost_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.call_connection_lost";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"user_id"         => \Entity_Validator_Structure::TYPE_INT,
		"call_map"        => \Entity_Validator_Structure::TYPE_STRING,
		"connection_uuid" => \Entity_Validator_Structure::TYPE_STRING,
		"is_publisher"    => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $call_map, int $user_id, string $connection_uuid, int $is_publisher):Struct_Sender_Event {

		return self::_buildEvent([
			"user_id"         => (int) $user_id,
			"call_map"        => (string) $call_map,
			"connection_uuid" => (string) $connection_uuid,
			"is_publisher"    => (int) $is_publisher,
		]);
	}
}