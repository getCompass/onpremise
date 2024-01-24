<?php

namespace Compass\Pivot;

/**
 * класс описывающий событие event.snoozed_timer_changed версии 1
 */
class Gateway_Bus_SenderBalancer_Event_SnoozedTimerChanged_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

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
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $snoozed_until):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"snoozed_until" => (int) $snoozed_until,
		]);
	}
}