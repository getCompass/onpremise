<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.all_conversations_messages_read версии 1
 */
class Gateway_Bus_Sender_Event_AllConversationsMessagesRead_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.all_conversations_messages_read";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"left_menu_version" => \Entity_Validator_Structure::TYPE_INT,
		"filter_favorites"  => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $left_menu_version, int $filter_favorites):Struct_Sender_Event {

		return self::_buildEvent([
			"left_menu_version" => (int) $left_menu_version,
			"filter_favorites"  => (int) $filter_favorites,
		]);
	}
}