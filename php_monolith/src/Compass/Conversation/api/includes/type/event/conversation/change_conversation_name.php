<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_ChangeConversationName {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.change_conversation_name";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $conversation_map, string $conversation_name, array $users):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_ChangeConversationName::build($conversation_map, $conversation_name, $users);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_ChangeConversationName {

		return Struct_Event_Conversation_ChangeConversationName::build(...$event["event_data"]);
	}
}
