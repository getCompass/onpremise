<?php

namespace Compass\Jitsi;

/**
 * событие создания сингла в Jitsi
 * @package Compass\Jitsi
 */
class Gateway_Bus_SenderBalancer_Event_ConferenceCreated_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conference_created";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conference_data"         => \Entity_Validator_Structure::TYPE_OBJECT,
		"conference_joining_data" => \Entity_Validator_Structure::TYPE_OBJECT,
		"conference_member_data"  => \Entity_Validator_Structure::TYPE_OBJECT,
		"conference_creator_data" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(Struct_Api_Conference_Data $conference_data, Struct_Api_Conference_JoiningData $conference_joining_data, Struct_Api_Conference_MemberData $conference_member_data, Struct_Api_Conference_CreatorData $conference_creator_data):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"conference_data"         => (object) $conference_data->format(),
			"conference_joining_data" => (object) $conference_joining_data->format(),
			"conference_member_data"  => (object) $conference_member_data->format(),
			"conference_creator_data" => (object) $conference_creator_data->format(),
		]);
	}
}