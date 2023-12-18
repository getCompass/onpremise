<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.company.general_chat_setting_changed версии 1
 */
class Gateway_Bus_Sender_Event_AddToGeneralChatOnHiringSettingChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.space.add_to_general_chat_on_hiring_setting_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"is_add_to_general_chat_on_hiring" => \Entity_Validator_Structure::TYPE_BOOL,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $is_add_to_general_chat_on_hiring):Struct_Sender_Event {

		return self::_buildEvent([
			"is_add_to_general_chat_on_hiring" => (int) $is_add_to_general_chat_on_hiring,
		]);
	}
}