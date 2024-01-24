<?php

namespace Compass\Thread;

/**
 * класс описывающий событие event.thread_follow версии 1
 */
class Gateway_Bus_Sender_Event_ThreadFollow_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.thread_follow";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"thread_map" => \Entity_Validator_Structure::TYPE_STRING,
		"is_follow"  => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $thread_map):Struct_Sender_Event {

		return self::_buildEvent([
			"thread_map" => (string) $thread_map,
			"is_follow"  => (int) 1,
		]);
	}
}