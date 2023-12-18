<?php

namespace Compass\Conversation;

/**
 * Событие — пользователь ушел из компании.
 *
 * @event_category user_conversation
 * @event_name user_left_conversation
 */
class Type_Event_UserConversation_UserLeftConversation {

	/** @var string тип события */
	public const EVENT_TYPE = "user_conversation.user_left_conversation";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, string $conversation_map, int $left_at):Struct_Event_Base {

		$event_data = Struct_Event_UserConversation_UserLeftConversation::build($user_id, $conversation_map, $left_at);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_UserConversation_UserLeftConversation {

		return Struct_Event_UserConversation_UserLeftConversation::build(...$event["event_data"]);
	}
}
