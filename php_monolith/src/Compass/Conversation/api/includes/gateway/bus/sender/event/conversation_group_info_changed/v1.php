<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_group_info_changed версии 1
 */
class Gateway_Bus_Sender_Event_ConversationGroupInfoChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_group_info_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
		"group_name"       => \Entity_Validator_Structure::TYPE_STRING,
		"file_map"         => \Entity_Validator_Structure::TYPE_STRING,
		"description"      => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map, string $group_name, string $file_map, string $description):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map" => (string) $conversation_map,
			"group_name"       => (string) $group_name,
			"file_map"         => (string) $file_map,
			"description"      => (string) $description,
		]);
	}
}