<?php

namespace Compass\Company;

/**
 * класс описывающий событие event.userbot.smart_app_updated версии 1
 */
class Gateway_Bus_Sender_Event_UserbotSmartAppUpdated_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.userbot.smart_app_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"userbot_id" => \Entity_Validator_Structure::TYPE_STRING,
		"smart_app"  => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $userbot_id, array $smart_app):Struct_Sender_Event {

		return self::_buildEvent([
			"userbot_id" => (string) $userbot_id,
			"smart_app"  => (object) $smart_app,
		]);
	}
}