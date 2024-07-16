<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывающий событие event.phone.changed версии 1
 */
class Gateway_Bus_SenderBalancer_Event_PhoneChanged_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.phone.changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"phone_mask" => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @throws ParseFatalException
	 */
	public static function makeEvent(string $phone):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"phone_mask" => (string) Domain_User_Entity_Phone::getPhoneNumberMask($phone),
		]);
	}
}