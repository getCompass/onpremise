<?php

namespace Compass\Pivot;

/**
 * класс описывающий событие action.premium_updated версии 1
 */
class Gateway_Bus_SenderBalancer_Event_PremiumUpdated_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.premium_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent():Struct_SenderBalancer_Event {

		return self::_buildEvent([]);
	}
}