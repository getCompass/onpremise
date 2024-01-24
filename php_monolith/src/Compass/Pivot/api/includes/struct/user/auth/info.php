<?php

namespace Compass\Pivot;

/**
 * Структура данных об аутентификации
 *
 * Class Struct_User_Auth_SmsInfo
 */
class Struct_User_Auth_Info {

	public string $auth_map;
	public int    $next_resend;
	public int    $available_attempts;
	public int    $expire_at;
	public string $phone_mask;
	public int    $type;

	/**
	 * Struct_User_Auth_SmsInfo constructor.
	 *
	 */
	public function __construct(string $auth_map, int $next_resend, int $available_attempts, int $expires_at, string $phone_mask, int $type) {

		$this->auth_map           = $auth_map;
		$this->next_resend        = $next_resend;
		$this->available_attempts = $available_attempts;
		$this->expire_at          = $expires_at;
		$this->phone_mask         = $phone_mask;
		$this->type               = $type;
	}
}