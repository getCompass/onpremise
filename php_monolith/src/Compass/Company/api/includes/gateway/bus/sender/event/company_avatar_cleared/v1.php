<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.company.avatar_cleared версии 1
 */
class Gateway_Bus_Sender_Event_CompanyAvatarCleared_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.avatar_cleared";

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