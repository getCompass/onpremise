<?php

namespace Compass\Thread;

/**
 * класс описывающий событие action.hiring_request_thread_attached версии 1
 */
class Gateway_Bus_Sender_Event_HiringRequestThreadAttached_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.hiring_request_thread_attached";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"hiring_request_id" => \Entity_Validator_Structure::TYPE_INT,
		"thread_map"        => \Entity_Validator_Structure::TYPE_STRING,
		"thread_meta"       => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $hiring_request_id, string $thread_map, array $thread_meta):Struct_Sender_Event {

		return self::_buildEvent([
			"hiring_request_id" => (int) $hiring_request_id,
			"thread_map"        => (string) $thread_map,
			"thread_meta"       => (object) $thread_meta,
		]);
	}
}