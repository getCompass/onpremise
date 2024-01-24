<?php

namespace Compass\Announcement;

/**
 * класс описывающий событие action.announcement_published версии 1
 */
class Gateway_Bus_SenderBalancer_Event_AnnouncementPublished_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.announcement_published";

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