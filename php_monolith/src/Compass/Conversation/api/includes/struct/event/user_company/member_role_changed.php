<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события изменинии роли участника компании
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserCompany_MemberRoleChanged extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/** @var int роль до имениния */
	public int $before_role;

	/** @var int назначенная роль */
	public int $role;
	public int $created_at;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, int $before_role, int $role, int $created_at):static {

		return new static([
			"user_id"     => $user_id,
			"before_role" => $before_role,
			"role"        => $role,
			"created_at"  => $created_at,
		]);
	}
}
