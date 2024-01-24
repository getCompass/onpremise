<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_company_list_{1}
 */
class Struct_PivotUser_UserCompanyList {

	public int $user_id;
	public int $company_id;
	public int $status;
	public int $order;
	public int $created_at;
	public int $updated_at;

	/**
	 * Struct_PivotUser_UserCompanyList constructor
	 *
	 */
	public function __construct(
		int $user_id,
		int $company_id,
		int $status,
		int $order,
		int $created_at,
		int $updated_at
	) {

		$this->user_id    = $user_id;
		$this->company_id = $company_id;
		$this->status     = $status;
		$this->order      = $order;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
	}
}