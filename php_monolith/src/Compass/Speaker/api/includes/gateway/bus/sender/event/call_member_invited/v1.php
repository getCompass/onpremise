<?php

namespace Compass\Speaker;

/**
 * класс описывающий событие event.call_member_invited версии 1
 */
class Gateway_Bus_Sender_Event_CallMemberInvited_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.call_member_invited";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"call"               => \Entity_Validator_Structure::TYPE_OBJECT,
		"invited_by_user_id" => \Entity_Validator_Structure::TYPE_INT,
		"user_id"            => \Entity_Validator_Structure::TYPE_INT,
		"call_map"           => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $formatted_call, int $invited_by_user_id, int $opponent_user_id, string $call_map):Struct_Sender_Event {

		return self::_buildEvent([
			"user_id"            => (int) $opponent_user_id,
			"invited_by_user_id" => (int) $invited_by_user_id,
			"call_map"           => (string) $call_map,
			"call"               => (object) $formatted_call,
		]);
	}
}