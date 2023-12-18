<?php

namespace Compass\Conversation;

/**
 * Событие — отправляем системные сообщения от бота пользователю
 *
 * @event_category message
 * @event_name     send_system_message_list_to_user
 */
class Type_Event_Message_SendSystemMessageListToUser {

	/** @var string тип события */
	public const EVENT_TYPE = "message.send_system_message_list_to_user";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $bot_user_id, int $receiver_user_id, array $message_list):Struct_Event_Base {

		$event_data = Struct_Event_Message_SendSystemMessageListToUser::build($bot_user_id, $receiver_user_id, $message_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Message_SendSystemMessageListToUser {

		return Struct_Event_Message_SendSystemMessageListToUser::build(...$event["event_data"]);
	}
}
