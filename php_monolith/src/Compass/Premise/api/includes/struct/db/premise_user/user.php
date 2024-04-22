<?php declare(strict_types=1);

namespace Compass\Premise;

/**
 * Класс структура для таблица premise_user.user_list
 */
class Struct_Db_PremiseUser_User {

	public function __construct(
		public int $user_id,
		public int $npc_type_alias,
		public int $space_status,
		public int $has_premise_permissions,
		public int $premise_permissions,
		public int $created_at,
		public int $updated_at,
		public string $external_sso_id,
		public string $external_other1_id,
		public string $external_other2_id,
		public array $external_data,
		public array $extra
	) {
}
}