<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.user_company.user_join_company версии 1
 */
class Gateway_Bus_Sender_Event_UserJoinCompany_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.user_company.user_join_company";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"user_id"      => \Entity_Validator_Structure::TYPE_INT,
		"member_count" => \Entity_Validator_Structure::TYPE_INT,
		"guest_count"  => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $user_id, int $member_count, int $guest_count):Struct_Sender_Event {

		return self::_buildEvent([
			"user_id"      => (int) $user_id,
			"member_count" => (int) $member_count,
			"guest_count"  => (int) $guest_count,
		]);
	}
}