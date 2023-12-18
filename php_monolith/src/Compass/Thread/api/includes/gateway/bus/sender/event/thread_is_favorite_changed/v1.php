<?php

namespace Compass\Thread;

/**
 * класс описывающий событие event.thread_is_favorite_changed версии 1
 */
class Gateway_Bus_Sender_Event_ThreadIsFavoriteChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.thread_is_favorite_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"thread_map"  => \Entity_Validator_Structure::TYPE_STRING,
		"is_favorite" => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $thread_map, int $is_favorite):Struct_Sender_Event {

		return self::_buildEvent([
			"thread_map"  => (string) $thread_map,
			"is_favorite" => (int) $is_favorite,
		]);
	}
}