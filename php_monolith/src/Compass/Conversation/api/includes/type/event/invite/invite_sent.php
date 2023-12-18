<?php

namespace Compass\Conversation;

/**
 * Событие — пользователь ушел из компании.
 *
 * @event_category invite
 * @event_name invite_status_changed
 */
class Type_Event_Invite_InviteSent {

	/** @var string тип события */
	public const EVENT_TYPE = "invite.invite_sent";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $invite_map, int $user_id, string $conversation_map, int $sent_at):Struct_Event_Base {

		$event_data = Struct_Event_Invite_InviteSent::build($invite_map, $user_id, $conversation_map, $sent_at);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Invite_InviteSent {

		return Struct_Event_Invite_InviteSent::build(...$event["event_data"]);
	}
}
