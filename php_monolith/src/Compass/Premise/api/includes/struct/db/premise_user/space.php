<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Класс структура для таблица premise_user.space_list
 */
class Struct_Db_PremiseUser_Space {

	public function __construct(
		public int   $user_id,
		public int   $space_id,
		public int   $role_alias,
		public int   $permissions_alias,
		public int   $created_at,
		public int   $updated_at,
		public array $extra,
	) {
	}
}