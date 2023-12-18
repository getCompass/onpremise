<?php

namespace Compass\Conversation;

/**
 * Событие — бот получил сообщение в диалоге
 *
 * @event_category userbot
 * @event_name     on_message_received
 */
class Type_Event_Userbot_OnMessageReceived {

	/** @var string тип события */
	public const EVENT_TYPE = "userbot.on_message_received";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(array $userbot_id_list, int $sender_id, array $message_text_list, string $conversation_ma):Struct_Event_Base {

		$event_data = Struct_Event_Userbot_OnMessageReceived::build($userbot_id_list, $sender_id, $message_text_list, $conversation_ma);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 */
	public static function parse(array $event):Struct_Event_Userbot_OnMessageReceived {

		return Struct_Event_Userbot_OnMessageReceived::build(...$event["event_data"]);
	}
}
