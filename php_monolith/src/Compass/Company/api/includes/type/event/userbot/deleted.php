<?php

namespace Compass\Company;

/**
 * Событие — удаление бота
 *
 * @event_category userbot
 * @event_name     deleted
 */
class Type_Event_Userbot_Deleted {

	/** @var string тип события */
	public const EVENT_TYPE = "userbot.deleted";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $userbot_id, int $userbot_user_id, array $user_id_list, array $conversation_map_list, int $deleted_at):Struct_Event_Base {

		$event_data = Struct_Event_Userbot_Deleted::build($userbot_id, $userbot_user_id, $user_id_list, $conversation_map_list, $deleted_at);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Userbot_Deleted {

		return Struct_Event_Userbot_Deleted::build(...$event["event_data"]);
	}
}
