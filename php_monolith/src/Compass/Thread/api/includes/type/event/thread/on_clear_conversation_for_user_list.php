<?php

namespace Compass\Thread;

/**
 * Событие — очищен диалог для пользователей.
 *
 * @event_category member
 * @event_name     member_info_updated
 */
class Type_Event_Thread_OnClearConversationForUserList {

	/** @var string тип события */
	public const EVENT_TYPE = "thread.on_clear_conversation_for_user_list";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $conversation_map, array $user_id_list):Struct_Event_Base {

		$event_data = Struct_Event_Thread_OnClearConversationForUserList::build($conversation_map, $user_id_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Thread_OnClearConversationForUserList {

		return Struct_Event_Thread_OnClearConversationForUserList::build(...$event["event_data"]);
	}
}
