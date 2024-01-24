<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_security_{1}
 */
class Struct_Db_PivotUser_UserSecurity {

	public int    $user_id;
	public string $phone_number;
	public int    $created_at;
	public int    $updated_at;

	/**
	 * Struct_Db_PivotUserSecurity_UserSecurity constructor.
	 *
	 */
	public function __construct(int $user_id, string $phone_number, int $created_at, int $updated_at) {

		$this->user_id      = $user_id;
		$this->phone_number = $phone_number;
		$this->created_at   = $created_at;
		$this->updated_at   = $updated_at;
	}
}