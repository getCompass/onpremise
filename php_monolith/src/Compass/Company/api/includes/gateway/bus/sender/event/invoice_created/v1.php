<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.premium.invoice.created версии 1
 */
class Gateway_Bus_Sender_Event_InvoiceCreated_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.premium.invoice.created";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"created_by_user_id" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $created_by_user_id):Struct_Sender_Event {

		return self::_buildEvent([
			"created_by_user_id" => (int) $created_by_user_id,
		]);
	}
}