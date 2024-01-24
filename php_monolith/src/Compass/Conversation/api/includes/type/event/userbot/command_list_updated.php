<?php

namespace Compass\Conversation;

/**
 * Событие — обновление списка команд бота
 *
 * @event_category userbot
 * @event_name     command_list_updated
 */
class Type_Event_Userbot_CommandListUpdated {

	/** @var string тип события */
	public const EVENT_TYPE = "userbot.command_list_updated";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(array $userbot, int $userbot_user_id, array $user_id_list, array $conversation_map_list):Struct_Event_Base {

		$event_data = Struct_Event_Userbot_CommandListUpdated::build($userbot, $userbot_user_id, $user_id_list, $conversation_map_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Userbot_CommandListUpdated {

		return Struct_Event_Userbot_CommandListUpdated::build(...$event["event_data"]);
	}
}
