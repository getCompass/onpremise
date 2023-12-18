<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_invite_status_changed версии 1
 */
class Gateway_Bus_Sender_Event_ConversationInviteStatusChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_invite_status_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
		"invite_map"       => \Entity_Validator_Structure::TYPE_STRING,
		"invited_user_id"  => \Entity_Validator_Structure::TYPE_INT,
		"status"           => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map, string $invite_map, int $invited_user_id, string $status_title):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map" => (string) $conversation_map,
			"invite_map"       => (string) $invite_map,
			"invited_user_id"  => (int) $invited_user_id,
			"status"           => (string) $status_title,
		]);
	}
}