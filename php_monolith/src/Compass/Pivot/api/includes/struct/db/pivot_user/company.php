<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.company_list_{1}
 */
class Struct_Db_PivotUser_Company {

	public int   $user_id;
	public int   $company_id;
	public int   $is_has_pin;
	public int   $order;
	public int   $entry_id;
	public int   $created_at;
	public int   $updated_at;
	public array $extra;

	/**
	 * Struct_Db_PivotUser_UserCompany constructor.
	 *
	 */
	public function __construct(int $user_id, int $company_id, int $is_has_pin, int $order, int $entry_id, int $created_at, int $updated_at, array $extra) {

		$this->user_id    = $user_id;
		$this->company_id = $company_id;
		$this->is_has_pin = $is_has_pin;
		$this->order      = $order;
		$this->entry_id   = $entry_id;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->extra      = $extra;
	}
}