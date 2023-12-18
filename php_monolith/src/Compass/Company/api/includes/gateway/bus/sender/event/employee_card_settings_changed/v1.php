<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.company.config.employee_card_settings_changed версии 1
 */
class Gateway_Bus_Sender_Event_EmployeeCardSettingsChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.config.employee_card_settings_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"is_enabled" => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $is_enabled):Struct_Sender_Event {

		return self::_buildEvent([
			"is_enabled" => (int) $is_enabled,
		]);
	}
}