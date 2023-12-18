<?php

namespace Compass\Conversation;

/**
 * Событие — создаст single диалоги для пользователя
 *
 * @event_category conversation
 * @event_name     add_single_for_user_list
 */
class Type_Event_Conversation_AddSingleList {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.add_single_list";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, array $opponent_user_id_list, bool $is_hidden_for_user = true, bool $is_hidden_for_opponent = true):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_AddSingleList::build($user_id, $opponent_user_id_list, $is_hidden_for_user, $is_hidden_for_opponent);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_AddSingleList {

		return Struct_Event_Conversation_AddSingleList::build(...$event["event_data"]);
	}
}
