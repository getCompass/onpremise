<?php

namespace Compass\Premise;

/**
 * Класс описывающий событие event.premise.server_activated версии 1
 */
class Gateway_Bus_Sender_Event_ServerActivated_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.premise.server_activated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [];

	/**
	 * собираем объект ws события
	 *
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent():Struct_SenderBalancer_Event {

		return self::_buildEvent([]);
	}
}