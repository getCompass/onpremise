<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события изменинии permissions участника компании
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserCompany_MemberGroupMaskChanged extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/** @var int permissions до измениния */
	public int $before_permissions;

	/** @var int назначенная permissions */
	public int $permissions;
	public int $created_at;

	/**
	 * Статический конструктор.
	 *
	 * @param int $user_id
	 * @param int $before_permissions
	 * @param int $permissions
	 * @param int $created_at
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function build(int $user_id, int $before_permissions, int $permissions, int $created_at):static {

		return new static([
			"user_id"           => $user_id,
			"before_permissions" => $before_permissions,
			"permissions"        => $permissions,
			"created_at"        => $created_at,
		]);
	}
}
