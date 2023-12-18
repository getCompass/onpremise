<?php

namespace Compass\Pivot;

/**
 * класс описывающий событие event.company.awoke версии 1
 */
class Gateway_Bus_SenderBalancer_Event_CompanyAwoke_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.awoke";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"company_id" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $company_id):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"company_id" => (int) $company_id,
		]);
	}
}