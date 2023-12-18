<?php

namespace Compass\Conversation;

/**
 * Событие — редактирование бота
 *
 * @event_category userbot
 * @event_name     edited
 */
class Type_Event_Userbot_Edited {

	/** @var string тип события */
	public const EVENT_TYPE = "userbot.edited";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(array $userbot, int $userbot_user_id, array $user_id_list, array $conversation_map_list):Struct_Event_Base {

		$event_data = Struct_Event_Userbot_Edited::build($userbot, $userbot_user_id, $user_id_list, $conversation_map_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Userbot_Edited {

		return Struct_Event_Userbot_Edited::build(...$event["event_data"]);
	}
}
