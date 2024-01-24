<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовая структура события «создать и отправить приглашение».
 * Работает с Invite, но не Invitation.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Invite_CreateAndSendInvite extends Struct_Default {

	/** @var int ид пользователя-отправителя */
	public int $sender_user_id;

	/** @var array user_id_list */
	public array $user_id_list;

	/** @var array meta_row */
	public array $meta_row;

	/**
	 * Статический конструктор.
	 *
	 * @param int   $sender_user_id
	 * @param array $user_id_list
	 * @param array $meta_row
	 *
	 * @return Struct_Event_Invite_CreateAndSendInvite
	 * @throws ParseFatalException
	 */
	public static function build(int $sender_user_id, array $user_id_list, array $meta_row):static {

		return new static([
			"sender_user_id" => $sender_user_id,
			"user_id_list"   => $user_id_list,
			"meta_row"       => $meta_row,
		]);
	}
}
