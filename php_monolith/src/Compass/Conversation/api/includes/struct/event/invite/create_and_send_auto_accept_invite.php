<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовая структура события «создать и отправить приглашение».
 * Работает с Invite, но не Invitation.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Invite_CreateAndSendAutoAcceptInvite extends Struct_Default {

	/** @var int ид пользователя-отправителя */
	public int $sender_user_id;

	/** @var array user_id_list */
	public array $user_id_list;

	/** @var array meta_row */
	public array $meta_row;

	/** @var string platform */
	public string $platform;

	/**
	 * Статический конструктор.
	 *
	 * @param int    $sender_user_id
	 * @param array  $user_id_list
	 * @param array  $meta_row
	 * @param string $platform
	 *
	 * @return Struct_Event_Invite_CreateAndSendAutoAcceptInvite
	 * @throws ParseFatalException
	 */
	public static function build(int $sender_user_id, array $user_id_list, array $meta_row, string $platform):static {

		return new static([
			"sender_user_id" => $sender_user_id,
			"user_id_list"   => $user_id_list,
			"meta_row"       => $meta_row,
			"platform"       => $platform,
		]);
	}
}
