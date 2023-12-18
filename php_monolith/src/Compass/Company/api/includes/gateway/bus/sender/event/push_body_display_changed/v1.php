<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.company.config.push_body_display_changed версии 1
 */
class Gateway_Bus_Sender_Event_PushBodyDisplayChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.config.push_body_display_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"is_display_push_body" => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $is_display_push_body):Struct_Sender_Event {

		return self::_buildEvent([
			"is_display_push_body" => (int) $is_display_push_body,
		]);
	}
}