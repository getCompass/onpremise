<?php

namespace Compass\Jitsi;

/**
 * событие обновления активной конференции пользователя
 * @package Compass\Jitsi
 */
class Gateway_Bus_SenderBalancer_Event_ActiveConferenceUpdated_V2 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var string действия, которые могут произойти с активной конференцией */
	public const ACTION_JOIN_CONFERENCE         = "join_conference";
	public const ACTION_LEFT_CONFERENCE         = "left_conference";
	public const ACTION_LOST_CONNECTION         = "lost_connection";
	public const ACTION_UPDATED_MEMBER_DATA     = "updated_member_data";
	public const ACTION_UPDATED_CONFERENCE_DATA = "updated_conference_data";

	/** @var int название метода */
	protected const _WS_EVENT = "event.active_conference_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 2;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"action"          => \Entity_Validator_Structure::TYPE_STRING,
		"conference_data" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $action, Struct_Api_Conference_Data $conference_data,
						   ?Struct_Api_Conference_MemberData $conference_member_data,
						   ?Struct_Api_Conference_JoiningData $joining_data,
						   ?int $conference_active_at):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"action"                  => (string) $action,
			"conference_data"         => (object) $conference_data->format(),
			"conference_member_data"  => !is_null($conference_member_data) ? $conference_member_data->format() : null,
			"conference_joining_data" => !is_null($joining_data) ? $joining_data->format() : null,
			"conference_active_at"    => !is_null($conference_active_at) ? (int) $conference_active_at : null,
		]);
	}
}