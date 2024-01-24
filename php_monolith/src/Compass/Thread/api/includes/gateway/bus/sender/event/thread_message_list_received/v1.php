<?php

namespace Compass\Thread;

/**
 * класс описывающий событие action.thread_message_list_received версии 1
 */
class Gateway_Bus_Sender_Event_ThreadMessageListReceived_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.thread_message_list_received";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message_list"            => \Entity_Validator_Structure::TYPE_ARRAY,
		"thread_meta"             => \Entity_Validator_Structure::TYPE_OBJECT,
		"follower_list"           => \Entity_Validator_Structure::TYPE_ARRAY,
		"location_type"           => \Entity_Validator_Structure::TYPE_STRING,
		"conversation_map"        => \Entity_Validator_Structure::TYPE_STRING,
		"threads_updated_version" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $message_list, array $thread_meta, array $follower_list, string $location_type, string $conversation_map, int $threads_updated_version):Struct_Sender_Event {

		return self::_buildEvent([
			"message_list"            => (array) $message_list,
			"thread_meta"             => (object) $thread_meta,
			"follower_list"           => (array) $follower_list,
			"location_type"           => (string) $location_type,
			"conversation_map"        => (string) $conversation_map,
			"threads_updated_version" => (int) $threads_updated_version,
		]);
	}
}