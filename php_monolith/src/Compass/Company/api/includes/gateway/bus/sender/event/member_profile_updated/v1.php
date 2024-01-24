<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.company.member.profile_updated версии 1
 */
class Gateway_Bus_Sender_Event_MemberProfileUpdated_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.member.profile_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"member_info"        => \Entity_Validator_Structure::TYPE_OBJECT,
		"client_launch_uuid" => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $formatted_member, string $client_launch_uuid):Struct_Sender_Event {

		return self::_buildEvent([
			"member_info"        => (object) $formatted_member,
			"client_launch_uuid" => (string) $client_launch_uuid,
		]);
	}
}