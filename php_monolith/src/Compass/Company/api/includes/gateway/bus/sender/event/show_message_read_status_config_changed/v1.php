<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс описывающий событие event.space.config.show_message_read_status версии 1
 */
class Gateway_Bus_Sender_Event_ShowMessageReadStatusConfigChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.space.config.show_message_read_status_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"show_message_read_status" => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * собираем объект ws события
	 *
	 * @param int $show_message_read_status
	 *
	 * @return Struct_Sender_Event
	 * @throws ParseFatalException
	 */
	public static function makeEvent(int $show_message_read_status):Struct_Sender_Event {

		return self::_buildEvent([
			"show_message_read_status" => (int) $show_message_read_status,
		]);
	}
}