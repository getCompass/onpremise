<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие — пользователь присоединился к диалогу.
 *
 * @event_category user_conversation
 * @event_name     user_joined_conversation
 */
class Type_Event_UserConversation_UserJoinedConversation {

	/** @var string тип события */
	public const EVENT_TYPE = "user_conversation.user_joined_conversation";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $role
	 * @param int    $joined_at
	 * @param bool   $is_silent_joining
	 *
	 * @return Struct_Event_Base
	 * @throws ParseFatalException
	 */
	public static function create(int $user_id, string $conversation_map, int $role, int $joined_at, bool $is_silent_joining):Struct_Event_Base {

		$event_data = Struct_Event_UserConversation_UserJoinedConversation::build($user_id, $conversation_map, $role, $joined_at, $is_silent_joining);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_UserConversation_UserJoinedConversation
	 * @throws ParseFatalException
	 */
	public static function parse(array $event):Struct_Event_UserConversation_UserJoinedConversation {

		return Struct_Event_UserConversation_UserJoinedConversation::build(...$event["event_data"]);
	}
}
