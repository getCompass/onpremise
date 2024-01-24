<?php

namespace Compass\Thread;

/**
 * класс описывающий событие event.all_threads_messages_read версии 1
 */
class Gateway_Bus_Sender_Event_AllThreadsMessagesRead_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.all_threads_messages_read";

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