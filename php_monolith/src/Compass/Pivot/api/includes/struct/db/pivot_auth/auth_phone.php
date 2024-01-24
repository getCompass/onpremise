<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_auth_{Y}.auth_phone_list_{m}
 */
class Struct_Db_PivotAuth_AuthPhone {

	public string $auth_map;
	public int    $is_success;
	public int    $resend_count;
	public int    $error_count;
	public int    $created_at;
	public int    $updated_at;
	public int    $next_resend_at;
	public string $sms_id;
	public string $sms_code_hash;
	public string $phone_number;

	/**
	 * Struct_Db_PivotAuth_AuthPhone constructor.
	 *
	 */
	public function __construct(
		string $auth_map,
		int    $is_success,
		int    $resend_count,
		int    $error_count,
		int    $created_at,
		int    $updated_at,
		int    $next_resend_at,
		string $sms_id,
		string $sms_code_hash,
		string $phone_number
	) {

		$this->auth_map       = $auth_map;
		$this->is_success     = $is_success;
		$this->resend_count   = $resend_count;
		$this->error_count    = $error_count;
		$this->created_at     = $created_at;
		$this->updated_at     = $updated_at;
		$this->next_resend_at = $next_resend_at;
		$this->sms_id         = $sms_id;
		$this->sms_code_hash  = $sms_code_hash;
		$this->phone_number   = $phone_number;
	}
}