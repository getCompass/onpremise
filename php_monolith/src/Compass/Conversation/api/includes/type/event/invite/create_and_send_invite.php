<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие на создание и отправку инвайта
 *
 * @event_category invite
 * @event_name     create_and_send_invite
 */
class Type_Event_Invite_CreateAndSendInvite {

	/** @var string тип события */
	public const EVENT_TYPE = "invite.create_and_send_invite";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param int   $sender_user_id
	 * @param array $user_id_list
	 * @param array $meta_row
	 *
	 * @return Struct_Event_Base
	 * @throws ParseFatalException
	 */
	public static function create(int $sender_user_id, array $user_id_list, array $meta_row):Struct_Event_Base {

		$event_data = Struct_Event_Invite_CreateAndSendInvite::build($sender_user_id, $user_id_list, $meta_row);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_Invite_CreateAndSendInvite
	 * @throws ParseFatalException
	 */
	public static function parse(array $event):Struct_Event_Invite_CreateAndSendInvite {

		return Struct_Event_Invite_CreateAndSendInvite::build(...$event["event_data"]);
	}
}
