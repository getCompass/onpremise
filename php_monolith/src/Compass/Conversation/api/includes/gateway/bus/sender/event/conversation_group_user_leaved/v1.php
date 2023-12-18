<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_group_user_leaved версии 1
 */
class Gateway_Bus_Sender_Event_ConversationGroupUserLeaved_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_group_user_leaved";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
		"user_id"          => \Entity_Validator_Structure::TYPE_INT,
		"talking_hash"     => \Entity_Validator_Structure::TYPE_STRING,
		"users"            => \Entity_Validator_Structure::TYPE_ARRAY,
		"leave_reason"     => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map, int $user_id, int $leave_reason, string $talking_hash, array $user_id_list):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map" => (string) $conversation_map,
			"user_id"          => (int) $user_id,
			"talking_hash"     => (string) $talking_hash,
			"users"            => (array) $user_id_list,
			"leave_reason"     => (string) Type_Conversation_LeftMenu::getLeaveReasonTitle($leave_reason),
		]);
	}
}