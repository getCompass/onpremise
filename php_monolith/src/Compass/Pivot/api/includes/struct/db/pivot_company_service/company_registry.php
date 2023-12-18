<?php

namespace Compass\Pivot;

/**
 * Класс структуры для записей базы pivot_company_service.company_registry
 */
class Struct_Db_PivotCompanyService_CompanyRegistry {

	/**
	 * Конструктор
	 *
	 * @param int  $company_id
	 * @param bool $is_busy
	 * @param bool $is_hibernated
	 * @param bool $is_mysql_alive
	 * @param int  $created_at
	 * @param int  $updated_at
	 */
	public function __construct(
		public int  $company_id,
		public bool $is_busy,
		public bool $is_hibernated,
		public bool $is_mysql_alive,
		public int  $created_at,
		public int  $updated_at,
	) {
	}

}