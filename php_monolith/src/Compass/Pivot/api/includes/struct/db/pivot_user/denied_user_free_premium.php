<?php

namespace Compass\Pivot;

/**
 * Класс-структура для таблицы pivot_user_{10m}.denied_user_free_premium
 */
class Struct_Db_PivotUser_DeniedUserFreePremium {

	public int $user_id;
	public int $created_at;
	public int $reason_type;

	/**
	 * Struct_Db_PivotUserSecurity_UserSecurity constructor.
	 *
	 */
	public function __construct(int $user_id, int $created_at, int $reason_type) {

		$this->user_id     = $user_id;
		$this->created_at  = $created_at;
		$this->reason_type = $reason_type;
	}
}