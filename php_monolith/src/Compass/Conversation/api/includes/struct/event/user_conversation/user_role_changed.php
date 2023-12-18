<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Базовая структура события «у пользователя сменилась роль».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserConversation_UserRoleChanged extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/** @var string ид диалога */
	public string $conversation_map;

	/** @var int роль */
	public int $role;

	/** @var int дата покидания диалога */
	public int $role_changed_at;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, string $conversation_map, int $role, int $role_changed_at):static {

		return new static([
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
			"role"             => $role,
			"role_changed_at"  => $role_changed_at,
		]);
	}
}
