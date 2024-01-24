<?php

namespace Compass\Conversation;

/**
 * Событие — отправляем системные сообщения от бота в чат
 *
 * @event_category message
 * @event_name     send_system_message_list_to_conversation
 */
class Type_Event_Message_SendSystemMessageListToConversation {

	/** @var string тип события */
	public const EVENT_TYPE = "message.send_system_message_list_to_conversation";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $bot_user_id, string $conversation_map, array $message_list):Struct_Event_Base {

		$event_data = Struct_Event_Message_SendSystemMessageListToConversation::build($bot_user_id, $conversation_map, $message_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Message_SendSystemMessageListToConversation {

		return Struct_Event_Message_SendSystemMessageListToConversation::build(...$event["event_data"]);
	}
}
