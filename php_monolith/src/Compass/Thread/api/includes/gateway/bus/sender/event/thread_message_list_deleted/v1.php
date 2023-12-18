<?php

namespace Compass\Thread;

/**
 * класс описывающий событие action.thread_message_list_deleted версии 1
 */
class Gateway_Bus_Sender_Event_ThreadMessageListDeleted_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.thread_message_list_deleted";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message_map_list" => \Entity_Validator_Structure::TYPE_ARRAY,
		"thread_map"       => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $message_map_list, string $thread_map):Struct_Sender_Event {

		return self::_buildEvent([
			"message_map_list" => (array) $message_map_list,
			"thread_map"       => (string) $thread_map,
		]);
	}
}