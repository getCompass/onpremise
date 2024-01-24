<?php

namespace Compass\Company;

use Entity_Validator_Structure;

/**
 * Класс описывающий событие event.user_company.guest_upgraded версии 1
 */
class Gateway_Bus_Sender_Event_GuestUpgraded_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.user_company.guest_upgraded";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"user_id"      => Entity_Validator_Structure::TYPE_INT,
		"member_count" => Entity_Validator_Structure::TYPE_INT,
		"guest_count"  => Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $user_id, int $member_count, int $guest_count):Struct_Sender_Event {

		return self::_buildEvent([
			"user_id"      => $user_id,
			"member_count" => $member_count,
			"guest_count"  => $guest_count,
		]);
	}
}