<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс описывающий событие event.smart_app.edited версии 1
 */
class Gateway_Bus_Sender_Event_SmartAppEdited_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.smart_app.edited";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"smart_app" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @param array $smart_app
	 *
	 * @return Struct_Sender_Event
	 * @throws ParseFatalException
	 */
	public static function makeEvent(array $smart_app):Struct_Sender_Event {

		return self::_buildEvent([
			"smart_app" => (object) $smart_app,
		]);
	}
}