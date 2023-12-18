<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.userbot.created версии 1
 */
class Gateway_Bus_Sender_Event_UserbotCreated_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.userbot.created";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"userbot" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $userbot):Struct_Sender_Event {

		return self::_buildEvent([
			"userbot" => (object) $userbot,
		]);
	}
}