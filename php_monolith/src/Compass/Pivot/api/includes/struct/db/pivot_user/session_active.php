<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.session_active_list_1
 */
class Struct_Db_PivotUser_SessionActive {

	/**
	 * Struct_Db_PivotUserSecurity_UserActiveSession constructor.
	 *
	 */
	public function __construct(
		public string $session_uniq,
		public int    $user_id,
		public int    $created_at,
		public int    $updated_at,
		public int    $login_at,
		public int    $refreshed_at,
		public string $ua_hash,
		public string $ip_address,
		public array  $extra
	) {
	}
}