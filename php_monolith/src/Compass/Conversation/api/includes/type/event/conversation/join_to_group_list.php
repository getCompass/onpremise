<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс вступления в список групп
 *
 * @event_category conversation
 * @event_name     join_to_group_list
 */
class Type_Event_Conversation_JoinToGroupList {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.join_to_group_list";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, array $conversation_map_list):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_JoinToGroupList::build($user_id, $conversation_map_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_JoinToGroupList {

		return Struct_Event_Conversation_JoinToGroupList::build(...$event["event_data"]);
	}
}
