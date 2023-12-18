<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_UpdateLastMessageOnMessageUpdate {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.update_last_message_on_message_update";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $conversation_map, array $message, array $users):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_UpdateLastMessageOnMessageUpdate::build($conversation_map, $message, $users);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_UpdateLastMessageOnMessageUpdate {

		return Struct_Event_Conversation_UpdateLastMessageOnMessageUpdate::build(...$event["event_data"]);
	}
}
