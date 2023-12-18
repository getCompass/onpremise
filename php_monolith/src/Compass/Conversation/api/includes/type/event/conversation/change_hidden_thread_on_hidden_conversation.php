<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_ChangeHiddenThreadOnHiddenConversation {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.change_hidden_thread_on_hidden_conversation";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, array $thread_map_list, bool $need_to_hide_parent_thread):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_ChangeHiddenThreadOnHiddenConversation::build($user_id, $thread_map_list, $need_to_hide_parent_thread);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_ChangeHiddenThreadOnHiddenConversation {

		return Struct_Event_Conversation_ChangeHiddenThreadOnHiddenConversation::build(...$event["event_data"]);
	}
}
