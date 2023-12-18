<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.dismissal_request_changed версии 1
 */
class Gateway_Bus_Sender_Event_DismissalRequestChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.dismissal_request_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"dismissal_request" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $dismissal_request):Struct_Sender_Event {

		return self::_buildEvent([
			"dismissal_request" => (object) $dismissal_request,
		]);
	}
}