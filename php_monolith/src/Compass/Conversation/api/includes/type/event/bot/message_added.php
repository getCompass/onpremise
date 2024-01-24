<?php

namespace Compass\Conversation;

/**
 * Событие — добавлена новая задача
 *
 * @event_category task
 * @event_name     task_added
 */
class Type_Event_Bot_MessageAdded {

	/** @var string тип события */
	public const EVENT_TYPE = "bot.message_added";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $bot_user_id, string $conversation_map, array $message_data):Struct_Event_Base {

		$event_data = Struct_Event_Bot_MessageAdded::build($bot_user_id, $conversation_map, $message_data);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Bot_MessageAdded {

		return Struct_Event_Bot_MessageAdded::build(...$event["event_data"]);
	}
}
