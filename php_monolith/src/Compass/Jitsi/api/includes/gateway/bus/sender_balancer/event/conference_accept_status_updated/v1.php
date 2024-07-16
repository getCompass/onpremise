<?php

namespace Compass\Jitsi;

use Entity_Validator_Structure;

/**
 * событие создания сингла в Jitsi
 * @package Compass\Jitsi
 */
class Gateway_Bus_SenderBalancer_Event_ConferenceAcceptStatusUpdated_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conference_accept_status_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conference_id" => \Entity_Validator_Structure::TYPE_STRING,
		"user_id"       => Entity_Validator_Structure::TYPE_INT,
		"accept_status" => Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conference_id, int $user_id, string $accept_status):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"conference_id" => (string) $conference_id,
			"accept_status" => (string) $accept_status,
			"user_id"       => (int) $user_id,
		]);
	}
}