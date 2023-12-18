<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_SendInviteToUser extends Struct_Default {

	public int $inviter_user_id;

	public int $invited_user_id;

	public string $invite_map;

	public string $singe_conversation_map;

	public array $group_meta_row;

	public bool $is_need_send_system_message;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(int $inviter_user_id, int $invited_user_id, string $invite_map, string $single_conversation_map, array $group_meta_row, bool $is_need_send_system_message):static {

		return new static([
			"inviter_user_id"             => $inviter_user_id,
			"invited_user_id"             => $invited_user_id,
			"invite_map"                  => $invite_map,
			"single_conversation_map"     => $single_conversation_map,
			"group_meta_row"              => $group_meta_row,
			"is_need_send_system_message" => $is_need_send_system_message,
		]);
	}
}
