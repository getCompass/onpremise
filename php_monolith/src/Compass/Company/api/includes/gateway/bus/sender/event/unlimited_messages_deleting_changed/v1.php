<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс описывающий событие event.company.unlimited_messages_deleting_changed версии 1
 */
class Gateway_Bus_Sender_Event_UnlimitedMessagesDeletingChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.unlimited_messages_deleting_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"is_unlimited_messages_deleting_enabled" => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * Собираем объект ws события
	 *
	 * @throws ParseFatalException
	 */
	public static function makeEvent(int $is_unlimited_messages_deleting_enabled):Struct_Sender_Event {

		return self::_buildEvent([
			"is_unlimited_messages_deleting_enabled" => (int) $is_unlimited_messages_deleting_enabled,
		]);
	}
}