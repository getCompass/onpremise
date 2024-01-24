<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_SetParentMessageIsDeleted {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.set_parent_message_is_deleted";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $thread_map):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_SetParentMessageIsDeleted::build($thread_map);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_SetParentMessageIsDeleted {

		return Struct_Event_Conversation_SetParentMessageIsDeleted::build(...$event["event_data"]);
	}
}
