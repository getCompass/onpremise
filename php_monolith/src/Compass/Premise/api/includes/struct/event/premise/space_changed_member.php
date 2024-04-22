<?php declare(strict_types = 1);

namespace Compass\Premise;

use parseException;

/**
 * Структура события изменения роли/прав пользователя в пространстве
 */
class Struct_Event_Premise_SpaceChangedMember extends Struct_Event_Default {

	/** @var int $user_id id пользователя */
	public int $user_id;

	/** @var int $role роль пользователя */
	public int $role;

	/** @var int $permissions права пользователя */
	public int $permissions;

	/** @var int $space_id id пространства */
	public int $space_id;

	/**
	 * Статический конструктор.
	 *
	 * @return static
	 * @throws parseException
	 */
	public static function build(int $user_id, int $role, int $permissions, int $space_id, string $unique_key = ""):static {

		return new static([
			"unique_key"  => $unique_key,
			"user_id"     => $user_id,
			"role"        => $role,
			"permissions" => $permissions,
			"space_id"    => $space_id,
		]);
	}
}
