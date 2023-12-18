<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.remind_deleted версии 1
 */
class Gateway_Bus_Sender_Event_RemindDeleted_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.remind_deleted";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"remind_id"                => \Entity_Validator_Structure::TYPE_INT,
		"message_map"              => \Entity_Validator_Structure::TYPE_STRING,
		"parent_type"              => \Entity_Validator_Structure::TYPE_STRING,
		"parent_key"               => \Entity_Validator_Structure::TYPE_STRING,
		"messages_updated_version" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $remind_id, string $message_map, string $parent_type, string $parent_key, int $messages_updated_version):Struct_Sender_Event {

		return self::_buildEvent([
			"remind_id"                => (int) $remind_id,
			"message_map"              => (string) $message_map,
			"parent_type"              => (string) $parent_type,
			"parent_key"               => (string) $parent_key,
			"messages_updated_version" => (int) $messages_updated_version,
		]);
	}
}