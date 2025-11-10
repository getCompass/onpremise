<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс описывающий событие event.smart_app.deleted версии 1
 */
class Gateway_Bus_Sender_Event_SmartAppDeleted_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.smart_app.deleted";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"smart_app_id" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @param int $smart_app_id
	 *
	 * @return Struct_Sender_Event
	 * @throws ParseFatalException
	 */
	public static function makeEvent(int $smart_app_id):Struct_Sender_Event {

		return self::_buildEvent([
			"smart_app_id" => (int) $smart_app_id,
		]);
	}
}