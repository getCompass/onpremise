<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Базовая структура события "начат процесс деактивации всех приглашений"
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Invite_DeactivateInvitesInitiated extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id):static {

		return new static([
			"user_id" => $user_id,
		]);
	}
}
