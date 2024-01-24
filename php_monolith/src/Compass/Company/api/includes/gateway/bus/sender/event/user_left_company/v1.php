<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.user_company.user_left_company версии 1
 */
class Gateway_Bus_Sender_Event_UserLeftCompany_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.user_company.user_left_company";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"user_id"      => \Entity_Validator_Structure::TYPE_INT,
		"reason"       => \Entity_Validator_Structure::TYPE_STRING,
		"member_count" => \Entity_Validator_Structure::TYPE_INT,
		"guest_count"  => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $user_id, string $reason, int $member_count, int $guest_count):Struct_Sender_Event {

		return self::_buildEvent([
			"user_id"      => $user_id,
			"reason"       => $reason,
			"member_count" => $member_count,
			"guest_count"  => $guest_count,
		]);
	}
}