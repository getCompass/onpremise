<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события изменинии permissions участника компании
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Member_PermissionsChanged extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/** @var int permissions до измениния */
	public int $before_role;
	public int $before_permissions;

	/** @var int назначенная permissions */
	public int $role;
	public int $permissions;
	public int $created_at;

	/**
	 * Статический конструктор.
	 *
	 * @param int $user_id
	 * @param int $before_role
	 * @param int $before_permissions
	 * @param int $role
	 * @param int $permissions
	 * @param int $created_at
	 *
	 * @return static
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function build(int $user_id, int $before_role, int $before_permissions, int $role, int $permissions, int $created_at):static {

		return new static([
			"user_id"            => $user_id,
			"before_role"        => $before_role,
			"before_permissions" => $before_permissions,
			"role"               => $role,
			"permissions"        => $permissions,
			"created_at"         => $created_at,
		]);
	}
}
