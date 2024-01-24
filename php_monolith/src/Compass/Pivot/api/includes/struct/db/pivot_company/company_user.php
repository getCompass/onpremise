<?php

namespace Compass\Pivot;

/**
 * класс-структура для пользователя в компании
 */
class Struct_Db_PivotCompany_CompanyUser {

	public int   $company_id;
	public int   $user_id;
	public int   $created_at;
	public int   $updated_at;
	public array $extra;

	/**
	 * Struct_UserCompany constructor.
	 *
	 */
	public function __construct(int $company_id, int $user_id, int $created_at, int $updated_at, array $extra) {

		$this->company_id = $company_id;
		$this->user_id    = $user_id;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->extra      = $extra;
	}
}