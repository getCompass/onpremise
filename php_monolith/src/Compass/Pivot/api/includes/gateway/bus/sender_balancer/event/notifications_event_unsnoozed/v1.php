<?php

namespace Compass\Pivot;

/**
 * класс описывающий событие event.notifications_event_unsnoozed версии 1
 */
class Gateway_Bus_SenderBalancer_Event_NotificationsEventUnsnoozed_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.notifications_event_unsnoozed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"event_type" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $event_type):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"event_type" => (int) $event_type,
		]);
	}
}