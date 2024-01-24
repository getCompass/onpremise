<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_company.company_observer_{1}
 */
class Struct_Db_PivotCompany_CompanyObserver {

	public int   $company_id;
	public int   $need_work;
	public int   $created_at;
	public int   $updated_at;
	public array $data;

	/**
	 * Struct_Db_PivotCompany_CompanyObserver constructor
	 *
	 */
	public function __construct(
		int   $company_id,
		int   $need_work,
		int   $created_at,
		int   $updated_at,
		array $data
	) {

		$this->company_id = $company_id;
		$this->need_work  = $need_work;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->data       = $data;
	}
}