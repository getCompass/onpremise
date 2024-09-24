<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие на создание и отправку инвайта со статусом авто-принятого
 *
 * @event_category invite
 * @event_name     create_and_send_auto_accept_invite
 */
class Type_Event_Invite_CreateAndSendAutoAcceptInvite {

	/** @var string тип события */
	public const EVENT_TYPE = "invite.create_and_send_auto_accept_invite";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param int                        $sender_user_id
	 * @param Struct_Conversation_User[] $user_list
	 * @param array                      $meta_row
	 * @param string                     $platform
	 *
	 * @return Struct_Event_Base
	 * @throws ParseFatalException
	 */
	public static function create(int $sender_user_id, array $user_list, array $meta_row, string $platform):Struct_Event_Base {

		$event_user_list = array_map(fn(Struct_Conversation_User $user) => $user->toArray(), $user_list);
		$event_data      = Struct_Event_Invite_CreateAndSendAutoAcceptInvite::build($sender_user_id, $event_user_list, $meta_row, $platform);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_Invite_CreateAndSendAutoAcceptInvite
	 * @throws ParseFatalException
	 */
	public static function parse(array $event):Struct_Event_Invite_CreateAndSendAutoAcceptInvite {

		return Struct_Event_Invite_CreateAndSendAutoAcceptInvite::build(...$event["event_data"]);
	}
}
