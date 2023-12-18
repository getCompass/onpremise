<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_DoUnfollowThreadList {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.do_unfollow_thread_list";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, array $thread_map_list):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_DoUnfollowThreadList::build($user_id, $thread_map_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_DoUnfollowThreadList {

		return Struct_Event_Conversation_DoUnfollowThreadList::build(...$event["event_data"]);
	}
}
