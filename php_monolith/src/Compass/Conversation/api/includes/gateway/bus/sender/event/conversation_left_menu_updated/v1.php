<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_left_menu_updated версии 1
 */
class Gateway_Bus_Sender_Event_ConversationLeftMenuUpdated_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_left_menu_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"left_menu" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $formatted_left_menu_item):Struct_Sender_Event {

		return self::_buildEvent([
			"left_menu" => (object) $formatted_left_menu_item,
		]);
	}
}