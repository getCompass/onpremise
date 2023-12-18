<?php

namespace Compass\Thread;

/**
 * класс описывающий событие event.remind_created версии 1
 */
class Gateway_Bus_Sender_Event_RemindCreated_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.remind_created";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"remind_id"       => \Entity_Validator_Structure::TYPE_INT,
		"remind_at"       => \Entity_Validator_Structure::TYPE_INT,
		"creator_user_id" => \Entity_Validator_Structure::TYPE_INT,
		"message_map"     => \Entity_Validator_Structure::TYPE_STRING,
		"comment"         => \Entity_Validator_Structure::TYPE_STRING,
		"parent_type"     => \Entity_Validator_Structure::TYPE_STRING,
		"parent_key"      => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $remind_id, int $remind_at, int $creator_user_id, string $message_map, string $comment, string $parent_type, string $parent_key):Struct_Sender_Event {

		return self::_buildEvent([
			"remind_id"       => (int) $remind_id,
			"remind_at"       => (int) $remind_at,
			"creator_user_id" => (int) $creator_user_id,
			"message_map"     => (string) $message_map,
			"comment"         => (string) $comment,
			"parent_type"     => (string) $parent_type,
			"parent_key"      => (string) $parent_key,
		]);
	}
}