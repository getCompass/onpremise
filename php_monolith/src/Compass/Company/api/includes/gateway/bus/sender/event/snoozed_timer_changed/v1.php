<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.snoozed_timer_changed версии 1
 */
class Gateway_Bus_Sender_Event_SnoozedTimerChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.snoozed_timer_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"snoozed_until" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $snoozed_until):Struct_Sender_Event {

		return self::_buildEvent([
			"snoozed_until" => (int) $snoozed_until,
		]);
	}
}