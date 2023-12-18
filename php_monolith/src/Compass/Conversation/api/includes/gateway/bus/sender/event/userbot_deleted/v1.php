<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.userbot.deleted версии 1
 */
class Gateway_Bus_Sender_Event_UserbotDeleted_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.userbot.deleted";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"userbot_id" => \Entity_Validator_Structure::TYPE_STRING,
		"deleted_at" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $userbot_id, int $deleted_at):Struct_Sender_Event {

		return self::_buildEvent([
			"userbot_id" => (string) $userbot_id,
			"deleted_at" => (int) $deleted_at,
		]);
	}
}