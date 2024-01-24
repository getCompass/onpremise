<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_group_avatar_changed версии 1
 */
class Gateway_Bus_Sender_Event_ConversationGroupAvatarChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_group_avatar_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
		"file_map"         => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map, string $avatar_file_map):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map" => (string) $conversation_map,
			"file_map"         => (string) $avatar_file_map,
		]);
	}
}