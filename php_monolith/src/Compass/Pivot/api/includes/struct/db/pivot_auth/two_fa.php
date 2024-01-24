<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_auth_{Y}.2fa_list_{m}
 */
class Struct_Db_PivotAuth_TwoFa {

	/**
	 * Struct_Db_PivotAuth_TwoFa constructor.
	 *
	 * @param string $two_fa_map
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param int    $is_active
	 * @param int    $is_success
	 * @param int    $action_type
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $expires_at
	 */
	public function __construct(
		public string $two_fa_map,
		public int    $user_id,
		public int    $company_id,
		public int    $is_active,
		public int    $is_success,
		public int    $action_type,
		public int    $created_at,
		public int    $updated_at,
		public int    $expires_at
	) {

	}
}
