<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_auth_{Y}.auth_list_{m}
 */
class Struct_Db_PivotAuth_Auth {

	public string $auth_uniq;
	public int    $user_id;
	public int    $is_success;
	public int    $type;
	public int    $created_at;
	public int    $updated_at;
	public int    $expires_at;
	public string $ua_hash;
	public string $ip_address;

	/**
	 * Struct_Db_PivotAuth_AuthList constructor.
	 *
	 */
	public function __construct(
		string $auth_uniq,
		int    $user_id,
		int    $is_success,
		int    $type,
		int    $created_at,
		int    $updated_at,
		int    $expires_at,
		string $ua_hash,
		string $ip_address
	) {

		$this->auth_uniq  = $auth_uniq;
		$this->user_id    = $user_id;
		$this->is_success = $is_success;
		$this->type       = $type;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->expires_at = $expires_at;
		$this->ua_hash    = $ua_hash;
		$this->ip_address = $ip_address;
	}
}