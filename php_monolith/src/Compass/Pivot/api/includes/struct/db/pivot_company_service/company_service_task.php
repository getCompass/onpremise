<?php

namespace Compass\Pivot;

/**
 * Класс структуры для записей базы pivot_company_service.company_service_task
 */
class Struct_Db_PivotCompanyService_CompanyServiceTask {

	/**
	 * Конструктор
	 *
	 * @param int    $task_id
	 * @param bool   $is_failed
	 * @param int    $need_work
	 * @param int    $type
	 * @param int    $started_at
	 * @param int    $finished_at
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $company_id
	 * @param string $logs
	 * @param array  $data
	 */
	public function __construct(
		public int    $task_id,
		public bool   $is_failed,
		public int    $need_work,
		public int    $type,
		public int    $started_at,
		public int    $finished_at,
		public int    $created_at,
		public int    $updated_at,
		public int    $company_id,
		public string $logs,
		public array  $data,
	) {
	}

}