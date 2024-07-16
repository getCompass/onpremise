<?php

namespace Compass\Jitsi;

/**
 * событие обновления параметров конференции
 * @package Compass\Jitsi
 */
class Gateway_Bus_SenderBalancer_Event_ConferenceOptionsUpdated_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conference_options_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conference_data" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(Struct_Api_Conference_Data $conference_data):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"conference_data" => (object) $conference_data->format(),
		]);
	}
}