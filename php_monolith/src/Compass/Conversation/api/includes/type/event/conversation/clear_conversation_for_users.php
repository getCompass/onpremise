<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие — очистка диалога у пользователей
 *
 * @event_category conversation
 */
class Type_Event_Conversation_ClearConversationForUsers {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.clear_conversation_for_users";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param string $conversation_map
	 * @param array  $user_id_list
	 * @param int    $messages_updated_version
	 *
	 * @return Struct_Event_Base
	 * @throws ParseFatalException
	 */
	public static function create(string $conversation_map, array $user_id_list, int $messages_updated_version):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_ClearConversationForUsers::build($conversation_map, $user_id_list, $messages_updated_version);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_Conversation_ClearConversationForUsers
	 * @throws ParseFatalException
	 */
	public static function parse(array $event):Struct_Event_Conversation_ClearConversationForUsers {

		return Struct_Event_Conversation_ClearConversationForUsers::build(...$event["event_data"]);
	}
}
