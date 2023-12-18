<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_group_role_changed версии 1
 */
class Gateway_Bus_Sender_Event_ConversationGroupRoleChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_group_role_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
		"user_id"          => \Entity_Validator_Structure::TYPE_INT,
		"new_role"         => \Entity_Validator_Structure::TYPE_STRING,
		"previous_role"    => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map, int $user_id, string $new_role, string $previous_role):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map" => (string) $conversation_map,
			"user_id"          => (int) $user_id,
			"new_role"         => (string) $new_role,
			"previous_role"    => (string) $previous_role,
		]);
	}
}