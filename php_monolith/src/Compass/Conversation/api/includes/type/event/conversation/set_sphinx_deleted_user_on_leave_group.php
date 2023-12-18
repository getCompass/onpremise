<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_SetSphinxDeletedUserOnLeaveGroup {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.set_sphinx_deleted_user_on_leave_group";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, string $conversation_map):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_SetSphinxDeletedUserOnLeaveGroup::build($user_id, $conversation_map);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_SetSphinxDeletedUserOnLeaveGroup {

		return Struct_Event_Conversation_SetSphinxDeletedUserOnLeaveGroup::build(...$event["event_data"]);
	}
}
