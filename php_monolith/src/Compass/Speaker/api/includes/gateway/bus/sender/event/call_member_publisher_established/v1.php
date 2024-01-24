<?php

namespace Compass\Speaker;

/**
 * класс описывающий событие event.call_member_publisher_established версии 1
 */
class Gateway_Bus_Sender_Event_CallMemberPublisherEstablished_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.call_member_publisher_established";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"user_id"             => \Entity_Validator_Structure::TYPE_INT,
		"call_map"            => \Entity_Validator_Structure::TYPE_STRING,
		"sub_connection_data" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $sub_connection_data, int $user_id, string $call_map):Struct_Sender_Event {

		return self::_buildEvent([
			"user_id"             => (int) $user_id,
			"call_map"            => (string) $call_map,
			"sub_connection_data" => (object) $sub_connection_data,
		]);
	}
}