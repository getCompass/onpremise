<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие — изменилось название диалога
 *
 * @event_category conversation
 * @event_name     conversation_name_changed
 */
class Type_Event_Conversation_ConversationNameChanged {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.conversation_name_changed";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param string $conversation_map
	 * @param string $conversation_name
	 *
	 * @return Struct_Event_Base
	 * @throws ParseFatalException
	 */
	public static function create(string $conversation_map, string $conversation_name):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_ConversationNameChanged::build($conversation_map, $conversation_name);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 */
	public static function parse(array $event):Struct_Event_Conversation_ConversationNameChanged {

		return Struct_Event_Conversation_ConversationNameChanged::build(...$event["event_data"]);
	}
}
