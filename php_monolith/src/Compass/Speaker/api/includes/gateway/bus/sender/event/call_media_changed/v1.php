<?php

namespace Compass\Speaker;

/**
 * класс описывающий событие event.call_media_changed версии 1
 */
class Gateway_Bus_Sender_Event_CallMediaChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.call_media_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"call_map"     => \Entity_Validator_Structure::TYPE_STRING,
		"user_id"      => \Entity_Validator_Structure::TYPE_INT,
		"audio_active" => \Entity_Validator_Structure::TYPE_BOOL,
		"video_active" => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $call_map, int $user_id, int $audio_active, int $video_active):Struct_Sender_Event {

		return self::_buildEvent([
			"call_map"     => (string) $call_map,
			"user_id"      => (int) $user_id,
			"audio_active" => (int) $audio_active,
			"video_active" => (int) $video_active,
		]);
	}
}