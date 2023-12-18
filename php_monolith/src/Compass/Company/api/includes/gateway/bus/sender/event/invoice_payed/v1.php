<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.premium.invoice.payed версии 1
 */
class Gateway_Bus_Sender_Event_InvoicePayed_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.premium.invoice.payed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent():Struct_Sender_Event {

		return self::_buildEvent([]);
	}
}