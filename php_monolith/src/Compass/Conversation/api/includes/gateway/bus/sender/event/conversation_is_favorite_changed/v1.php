<?php

namespace Compass\Conversation;

/**
 * класс описывающий событие event.conversation_is_favorite_changed версии 1
 */
class Gateway_Bus_Sender_Event_ConversationIsFavoriteChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.conversation_is_favorite_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_map" => \Entity_Validator_Structure::TYPE_STRING,
		"is_favorite"      => \Entity_Validator_Structure::TYPE_BOOL,
		"left_menu"        => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $conversation_map, int $is_favorite, array $formatted_left_menu):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_map" => (string) $conversation_map,
			"is_favorite"      => (int) $is_favorite,
			"left_menu"        => (object) $formatted_left_menu,
		]);
	}
}