<?php

namespace Compass\Pivot;

/**
 * Класс структуры для записей базы pivot_company_service.company_init_registry
 */
class Struct_Db_PivotCompanyService_CompanyInitRegistry {

	/**
	 * Конструктор
	 *
	 * @param int   $company_id
	 * @param bool  $is_vacant
	 * @param bool  $is_deleted
	 * @param bool  $is_purged
	 * @param int   $creating_started_at
	 * @param int   $creating_finished_at
	 * @param int   $became_vacant_at
	 * @param int   $occupation_started_at
	 * @param int   $occupation_finished_at
	 * @param int   $deleted_at
	 * @param int   $purged_at
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param int   $occupant_user_id
	 * @param int   $deleter_user_id
	 * @param array $logs
	 * @param array $extra
	 */
	public function __construct(
		public int   $company_id,
		public bool  $is_vacant,
		public bool  $is_deleted,
		public bool  $is_purged,
		public int   $creating_started_at,
		public int   $creating_finished_at,
		public int   $became_vacant_at,
		public int   $occupation_started_at,
		public int   $occupation_finished_at,
		public int   $deleted_at,
		public int   $purged_at,
		public int   $created_at,
		public int   $updated_at,
		public int   $occupant_user_id,
		public int   $deleter_user_id,
		public array $logs,
		public array $extra,
	) {
	}

}