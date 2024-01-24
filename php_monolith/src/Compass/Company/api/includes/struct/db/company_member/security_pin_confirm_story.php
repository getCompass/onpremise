<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_member.security_pin_confirm_story
 */
class Struct_Db_CompanyMember_SecurityPinConfirmStory {

	public string $confirm_key;
	public int    $user_id;
	public int    $status;
	public int    $created_at;
	public int    $updated_at;
	public int    $expires_at;

	/**
	 * Struct_Db_CompanyMember_SecurityPinConfirmStory constructor.
	 */
	public function __construct(string $confirm_key, int $user_id, int $status, int $created_at, int $updated_at, int $expires_at) {

		$this->confirm_key = $confirm_key;
		$this->user_id     = $user_id;
		$this->status      = $status;
		$this->created_at  = $created_at;
		$this->updated_at  = $updated_at;
		$this->expires_at  = $expires_at;
	}
}