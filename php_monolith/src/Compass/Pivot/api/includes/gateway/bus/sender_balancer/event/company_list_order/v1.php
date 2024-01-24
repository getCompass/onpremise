<?php

namespace Compass\Pivot;

/**
 * класс описывающий событие event.company.list_order версии 1
 */
class Gateway_Bus_SenderBalancer_Event_CompanyListOrder_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.list_order";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"company_order_list" => \Entity_Validator_Structure::TYPE_ARRAY,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $company_order_list):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"company_order_list" => (array) $company_order_list,
		]);
	}
}