<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_data.company_task_queue
 */
class Struct_Db_PivotData_CompanyTaskQueue {

	public int   $company_task_id;
	public int   $company_id;
	public int   $type;
	public int   $status;
	public int   $need_work;
	public int   $iteration_count;
	public int   $error_count;
	public int   $created_at;
	public int   $updated_at;
	public int   $done_at;
	public array $extra;

	/**
	 * Struct_Db_PivotData_CompanyTaskQueue constructor.
	 *
	 * @param int   $company_task_id
	 * @param int   $company_id
	 * @param int   $type
	 * @param int   $status
	 * @param int   $need_work
	 * @param int   $iteration_count
	 * @param int   $error_count
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param int   $done_at
	 * @param array $extra
	 */
	public function __construct(int   $company_task_id,
					    int   $company_id,
					    int   $type,
					    int   $status,
					    int   $need_work,
					    int   $iteration_count,
					    int   $error_count,
					    int   $created_at,
					    int   $updated_at,
					    int   $done_at,
					    array $extra) {

		$this->company_task_id = $company_task_id;
		$this->company_id      = $company_id;
		$this->type            = $type;
		$this->status          = $status;
		$this->need_work       = $need_work;
		$this->iteration_count = $iteration_count;
		$this->error_count     = $error_count;
		$this->created_at      = $created_at;
		$this->updated_at      = $updated_at;
		$this->done_at         = $done_at;
		$this->extra           = $extra;
	}
}