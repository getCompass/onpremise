<?php

namespace Compass\Thread;

/**
 * класс описывающий событие event.thread_menu_item_updated версии 1
 */
class Gateway_Bus_Sender_Event_ThreadMenuItemUpdated_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.thread_menu_item_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"thread_menu_item" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $thread_menu_item):Struct_Sender_Event {

		return self::_buildEvent([
			"thread_menu_item" => (object) $thread_menu_item,
		]);
	}
}