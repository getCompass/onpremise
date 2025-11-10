<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс описывающий событие event.company.local_links_changed версии 1
 */
class Gateway_Bus_Sender_Event_LocalLinksChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.local_links_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"is_local_links_enabled" => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * Собираем объект ws события
	 *
	 * @throws ParseFatalException
	 */
	public static function makeEvent(int $is_local_links_enabled):Struct_Sender_Event {

		return self::_buildEvent([
			"is_local_links_enabled" => (int) $is_local_links_enabled,
		]);
	}
}