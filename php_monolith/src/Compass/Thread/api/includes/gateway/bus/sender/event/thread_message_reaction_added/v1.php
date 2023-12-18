<?php

namespace Compass\Thread;

/**
 * класс описывающий событие action.thread_message_reaction_added версии 1
 */
class Gateway_Bus_Sender_Event_ThreadMessageReactionAdded_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.thread_message_reaction_added";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message_map"        => \Entity_Validator_Structure::TYPE_STRING,
		"reaction_name"      => \Entity_Validator_Structure::TYPE_STRING,
		"user_id"            => \Entity_Validator_Structure::TYPE_INT,
		"updated_at_ms"      => \Entity_Validator_Structure::TYPE_INT,
		"client_launch_uuid" => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $message_map, string $reaction_name, int $user_id, int $updated_at_ms, string $client_launch_uuid):Struct_Sender_Event {

		return self::_buildEvent([
			"message_map"        => (string) $message_map,
			"reaction_name"      => (string) $reaction_name,
			"user_id"            => (int) $user_id,
			"updated_at_ms"      => (int) $updated_at_ms,
			"client_launch_uuid" => (string) $client_launch_uuid,
		]);
	}
}