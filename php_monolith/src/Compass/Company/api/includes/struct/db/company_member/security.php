<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_member.security_list
 */
class Struct_Db_CompanyMember_Security {

	public int    $user_id;
	public bool   $is_pin_required;
	public int    $created_at;
	public int    $updated_at;
	public int    $last_enter_pin_at;
	public int    $pin_hash_version;
	public string $pin_hash;

	/**
	 * Struct_Db_CompanyMember_Security constructor.
	 */
	public function __construct(int $user_id, bool $is_pin_required, int $created_at, int $updated_at, int $last_enter_pin_at, int $pin_hash_version, string $pin_hash) {

		$this->user_id           = $user_id;
		$this->is_pin_required   = $is_pin_required;
		$this->created_at        = $created_at;
		$this->updated_at        = $updated_at;
		$this->last_enter_pin_at = $last_enter_pin_at;
		$this->pin_hash_version  = $pin_hash_version;
		$this->pin_hash          = $pin_hash;
	}
}