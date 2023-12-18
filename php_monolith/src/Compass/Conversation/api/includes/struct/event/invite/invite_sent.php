<?php

namespace Compass\Conversation;

/**
 * Базовая структура события «приглашение отправлено».
 * Работает с Invite, но не Invitation.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Invite_InviteSent extends Struct_Default {

	/** @var string ид инвайта */
	public string $invite_map;

	/** @var int ид пользователя */
	public int $user_id;

	/** @var string conversation_map */
	public string $conversation_map;

	/** @var int дата вступления в компанию */
	public int $sent_at;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $invite_map, int $user_id, string $conversation_map, int $sent_at):static {

		return new static([
			"invite_map"       => $invite_map,
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
			"sent_at"          => $sent_at,
		]);
	}
}
