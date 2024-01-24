<?php

namespace Compass\Conversation;

/**
 * Базовая структура события «статус приглашения изменен».
 * Работает с Invite, но не Invitation.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Invite_InviteStatusChanged extends Struct_Default {

	/** @var string conversation_map */
	public string $invite_map;

	/** @var int ид пользователя */
	public int $user_id;

	/** @var string conversation_map */
	public string $conversation_map;

	/** @var int актуальный статус приглашения */
	public int $status;

	/** @var int дата вступления в компанию */
	public int $status_changed_at;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $invite_map, int $user_id, string $conversation_map, int $status, int $status_changed_at):static {

		return new static([
			"invite_map"        => $invite_map,
			"user_id"           => $user_id,
			"conversation_map"  => $conversation_map,
			"status"            => $status,
			"status_changed_at" => $status_changed_at,
		]);
	}
}
