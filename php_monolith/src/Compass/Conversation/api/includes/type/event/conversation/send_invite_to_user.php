<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_SendInviteToUser {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.send_invite_to_user";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $inviter_user_id, int $invited_user_id, string $invite_map, string $single_conversation_map, array $group_meta_row, bool $is_need_send_system_message):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_SendInviteToUser::build($inviter_user_id, $invited_user_id, $invite_map, $single_conversation_map, $group_meta_row, $is_need_send_system_message);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_SendInviteToUser {

		return Struct_Event_Conversation_SendInviteToUser::build(...$event["event_data"]);
	}
}
