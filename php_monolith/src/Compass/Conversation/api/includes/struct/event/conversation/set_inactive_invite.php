<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_SetInactiveInvite extends Struct_Default {

	public int $user_id;

	public string $conversation_map;

	public int $inactive_reason;

	public bool $is_remove_user;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(int $user_id, string $conversation_map, int $inactive_reason, bool $is_remove_user):static {

		return new static([
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
			"inactive_reason"  => $inactive_reason,
			"is_remove_user"   => $is_remove_user,
		]);
	}
}
