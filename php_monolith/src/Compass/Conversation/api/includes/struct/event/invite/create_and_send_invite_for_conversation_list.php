<?php

namespace Compass\Conversation;

/**
 * Базовая структура события «создать и отправить приглашение в несоклько групп».
 * Работает с Invite, но не Invitation.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Invite_CreateAndSendInviteForConversationList extends Struct_Default {

	public int   $sender_user_id;
	public int   $user_id;
	public array $conversation_map_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $sender_user_id, int $user_id, array $conversation_map_list):static {

		return new static([
			"sender_user_id"        => $sender_user_id,
			"user_id"               => $user_id,
			"conversation_map_list" => $conversation_map_list,
		]);
	}
}
