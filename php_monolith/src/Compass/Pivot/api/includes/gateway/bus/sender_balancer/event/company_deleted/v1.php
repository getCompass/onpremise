<?php

namespace Compass\Pivot;

/**
 * класс описывающий событие event.company.deleted версии 1
 */
class Gateway_Bus_SenderBalancer_Event_CompanyDeleted_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.deleted";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"company_id" => \Entity_Validator_Structure::TYPE_INT,
		"deleted_at" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $company_id, int $deleted_at):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"company_id" => (int) $company_id,
			"deleted_at" => (int) $deleted_at,
		]);
	}
}