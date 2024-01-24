<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_AddMessage {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.add_message";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $conversation_map, array $message, array $users, int $conversation_type, string $conversation_name, array $conversation_extra):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_AddMessage::build($conversation_map, $message, $users, $conversation_type, $conversation_name, $conversation_extra);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_AddMessage {

		return Struct_Event_Conversation_AddMessage::build(...$event["event_data"]);
	}
}
