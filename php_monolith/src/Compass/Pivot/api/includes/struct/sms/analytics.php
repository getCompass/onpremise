<?php

namespace Compass\Pivot;

/**
 * класс-агрегат для информации по аналитике смс
 */
class Struct_Sms_Analytics {

	/**
	 * Struct_Sms_Analytics constructor.
	 *
	 * @param string $uuid
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param int    $story_type
	 * @param string $story_id
	 * @param string $sms_id
	 * @param string $phone_number
	 * @param int    $created_at
	 * @param int    $expires_at
	 * @param int    $type_id
	 * @param string $status_response
	 * @param string $sms_provider_id
	 * @param string $status_provider
	 * @param string $phone_number_operator
	 */
	public function __construct(
		public string $uuid,
		public int    $user_id,
		public int    $company_id,
		public int    $story_type,
		public string $story_id,
		public string $sms_id,
		public string $phone_number,
		public int    $created_at,
		public int    $expires_at,
		public int    $type_id,
		public string $status_response,
		public string $sms_provider_id,
		public string $status_provider,
		public string $phone_number_operator
	) {

	}
}
