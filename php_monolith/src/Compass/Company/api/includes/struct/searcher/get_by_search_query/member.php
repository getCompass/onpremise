<?php

namespace Compass\Company;

/**
 * Класс-структура для индекса мантикоры для пользователя
 */
class Struct_Searcher_GetBySearchQuery_Member {

	public int $user_id;
	public int $role;
	public int $permissions;

	/**
	 * Struct_Searcher_GetBySearchQuery_Member constructor.
	 *
	 * @param string $user_id
	 * @param string $role
	 * @param string $permissions
	 */
	public function __construct(string $user_id, string $role, string $permissions) {

		$this->user_id     = (int) $user_id;
		$this->role        = (int) $role;
		$this->permissions = (int) $permissions;
	}
}
