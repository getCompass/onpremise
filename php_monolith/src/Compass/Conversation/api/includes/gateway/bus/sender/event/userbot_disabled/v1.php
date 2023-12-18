<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.userbot.disabled версии 1
 */
class Gateway_Bus_Sender_Event_UserbotDisabled_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.userbot.disabled";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"userbot_id"  => \Entity_Validator_Structure::TYPE_STRING,
		"disabled_at" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $userbot_id, int $disabled_at):Struct_Sender_Event {

		return self::_buildEvent([
			"userbot_id"  => (string) $userbot_id,
			"disabled_at" => (int) $disabled_at,
		]);
	}
}