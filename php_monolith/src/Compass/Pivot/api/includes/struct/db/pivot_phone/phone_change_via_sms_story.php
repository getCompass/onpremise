<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_phone.phone_change_via_sms_story
 */
class Struct_Db_PivotPhone_PhoneChangeViaSmsStory {

	/**
	 * Struct_Db_PivotPhone_PhoneChangeViaSmsStory constructor.
	 *
	 * @param int    $change_phone_story_id
	 * @param string $phone_number
	 * @param int    $status
	 * @param int    $stage
	 * @param int    $resend_count
	 * @param int    $error_count
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $next_resend_at
	 * @param string $sms_id
	 * @param string $sms_code_hash
	 */
	public function __construct(
		public int    $change_phone_story_id,
		public string $phone_number,
		public int    $status,
		public int    $stage,
		public int    $resend_count,
		public int    $error_count,
		public int    $created_at,
		public int    $updated_at,
		public int    $next_resend_at,
		public string $sms_id,
		public string $sms_code_hash,
	) {

	}
}
