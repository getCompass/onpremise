<?php

namespace Compass\Jitsi;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_list_{1}
 */
class Struct_Db_PivotUser_User {

	public function __construct(
		public int    $user_id,
		public int    $npc_type,
		public int    $invited_by_partner_id,
		public int    $invited_by_user_id,
		public int    $last_active_day_start_at,
		public int    $created_at,
		public int    $updated_at,
		public int    $full_name_updated_at,
		public string $country_code,
		public string $full_name,
		public string $avatar_file_map,
		public array  $extra,
	) {
	}
}