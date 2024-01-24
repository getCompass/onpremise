<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_ChangeUserRoleInGroup {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.change_user_role_in_group";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, string $conversation_map, int $role):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_ChangeUserRoleInGroup::build($user_id, $conversation_map, $role);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_ChangeUserRoleInGroup {

		return Struct_Event_Conversation_ChangeUserRoleInGroup::build(...$event["event_data"]);
	}
}
