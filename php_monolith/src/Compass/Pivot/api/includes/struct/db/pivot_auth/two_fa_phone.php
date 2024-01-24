<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_auth_{Y}.2fa_phone_list_{m}
 */
class Struct_Db_PivotAuth_TwoFaPhone {

	/**
	 * Struct_Db_PivotAuth_TwoFaPhone constructor.
	 *
	 * @param string $two_fa_map
	 * @param int    $is_success
	 * @param int    $resend_count
	 * @param int    $error_count
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $next_resend_at
	 * @param string $sms_id
	 * @param string $sms_code_hash
	 * @param string $phone_number
	 */
	public function __construct(
		public string $two_fa_map,
		public int    $is_success,
		public int    $resend_count,
		public int    $error_count,
		public int    $created_at,
		public int    $updated_at,
		public int    $next_resend_at,
		public string $sms_id,
		public string $sms_code_hash,
		public string $phone_number
	) {

	}
}
