<?php

namespace Compass\FileBalancer;

/**
 * класс описывающий событие event.file_voice_listen версии 1
 */
class Gateway_Bus_Sender_Event_FileVoiceListen_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.file_voice_listen";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"file_map" => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $file_map):Struct_Sender_Event {

		return self::_buildEvent([
			"file_map" => (string) $file_map,
		]);
	}
}