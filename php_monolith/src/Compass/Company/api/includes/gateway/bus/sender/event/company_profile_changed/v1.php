<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.company.profile_changed версии 1
 */
class Gateway_Bus_Sender_Event_CompanyProfileChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.profile_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"?name"            => \Entity_Validator_Structure::TYPE_STRING,
		"?avatar_color_id" => \Entity_Validator_Structure::TYPE_INT,
		"?avatar_file_key" => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string|false $name, int|false $avatar_color_id, string|false $avatar_file_key):Struct_Sender_Event {

		$event_data = [];

		if ($name !== false) {
			$event_data["name"] = (string) $name;
		}

		if ($avatar_color_id !== false) {
			$event_data["avatar_color_id"] = (int) $avatar_color_id;
		}

		if ($avatar_file_key !== false) {
			$event_data["avatar_file_key"] = (string) $avatar_file_key;
		}

		return self::_buildEvent($event_data);
	}
}