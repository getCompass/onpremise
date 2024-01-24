<?php

namespace Compass\Pivot;

/**
 * класс описывающий событие event.company.status_changed версии 1
 */
class Gateway_Bus_SenderBalancer_Event_CompanyStatusChanged_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.status_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"company" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $company):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"company" => (object) $company,
		]);
	}
}