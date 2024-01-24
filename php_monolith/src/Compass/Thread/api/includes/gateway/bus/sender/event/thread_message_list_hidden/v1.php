<?php

namespace Compass\Thread;

/**
 * класс описывающий событие event.thread_message_hidden версии 1
 */
class Gateway_Bus_Sender_Event_ThreadMessageListHidden_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.thread_message_list_hidden";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message_map_list"        => \Entity_Validator_Structure::TYPE_ARRAY,
		"thread_map"              => \Entity_Validator_Structure::TYPE_STRING,
		"conversation_map"        => \Entity_Validator_Structure::TYPE_STRING,
		"threads_updated_version" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $message_map_list, string $thread_map, string $conversation_map, int $threads_updated_version):Struct_Sender_Event {

		return self::_buildEvent([
			"message_map_list"        => (array) $message_map_list,
			"thread_map"              => (string) $thread_map,
			"conversation_map"        => (string) $conversation_map,
			"threads_updated_version" => (int) $threads_updated_version,
		]);
	}
}