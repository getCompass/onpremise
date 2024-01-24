<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.premium.payment_request.deleted версии 1
 */
class Gateway_Bus_Sender_Event_PremiumPaymentRequestDeleted_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.premium.payment_request.deleted";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"unread_count" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $unread_count):Struct_Sender_Event {

		return self::_buildEvent([
			"unread_count" => (int) $unread_count,
		]);
	}
}