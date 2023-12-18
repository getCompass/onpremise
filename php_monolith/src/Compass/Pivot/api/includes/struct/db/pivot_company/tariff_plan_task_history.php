<?php

namespace Compass\Pivot;

/**
 * Класс структуры для записей базы pivot_company_{10m}.tariff_plan_task_history
 */
class Struct_Db_PivotCompany_TariffPlanTaskHistory {

	/**
	 * Конструктор
	 *
	 * @param int    $id
	 * @param int    $space_id
	 * @param int    $type
	 * @param int    $status
	 * @param int    $in_queue_time
	 * @param int    $created_at
	 * @param string $logs
	 * @param array  $extra
	 */
	public function __construct(
		public int    $id,
		public int    $space_id,
		public int    $type,
		public int    $status,
		public int    $in_queue_time,
		public int    $created_at,
		public string $logs,
		public array  $extra,
	) {
	}

}