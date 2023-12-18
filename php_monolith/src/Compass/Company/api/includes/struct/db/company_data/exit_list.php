<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.exit_list
 */
class Struct_Db_CompanyData_ExitList {

	public int   $exit_id;
	public int   $user_id;
	public int   $status;
	public int   $step;
	public int   $created_at;
	public int   $updated_at;
	public array $data;
	public array $extra;

	/**
	 * Struct_Db_CompanyData_ExitList constructor.
	 *
	 * @param int   $exit_id
	 * @param int   $user_id
	 * @param int   $status
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param int   $step
	 * @param array $extra
	 */
	public function __construct(int   $exit_id,
					    int   $user_id,
					    int   $status,
					    int   $step,
					    int   $created_at,
					    int   $updated_at,
					    array $extra) {

		$this->exit_id    = $exit_id;
		$this->user_id    = $user_id;
		$this->status     = $status;
		$this->step       = $step;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->extra      = $extra;
	}
}