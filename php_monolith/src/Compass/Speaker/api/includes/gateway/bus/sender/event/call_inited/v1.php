<?php

namespace Compass\Speaker;

/**
 * класс описывающий событие event.call_inited версии 1
 */
class Gateway_Bus_Sender_Event_CallInited_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.call_inited";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"call" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $formatted_call):Struct_Sender_Event {

		return self::_buildEvent([
			"call" => (object) $formatted_call,
		]);
	}
}