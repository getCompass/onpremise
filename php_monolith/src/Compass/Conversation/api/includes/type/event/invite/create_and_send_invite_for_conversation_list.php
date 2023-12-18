<?php

namespace Compass\Conversation;

/**
 * Событие на создание и отправку инвайта в список групп
 *
 * @event_category invite
 * @event_name     create_and_send_invite
 */
class Type_Event_Invite_CreateAndSendInviteForConversationList {

	/** @var string тип события */
	public const EVENT_TYPE = "invite.create_and_send_invite_for_conversation_list";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $sender_user_id, int $user_id, array $conversation_map_list):Struct_Event_Base {

		$event_data = Struct_Event_Invite_CreateAndSendInviteForConversationList::build($sender_user_id, $user_id, $conversation_map_list);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Invite_CreateAndSendInviteForConversationList {

		return Struct_Event_Invite_CreateAndSendInviteForConversationList::build(...$event["event_data"]);
	}
}
