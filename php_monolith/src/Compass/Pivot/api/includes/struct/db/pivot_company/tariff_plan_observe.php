<?php

namespace Compass\Pivot;

/**
 * Класс структуры для записей базы pivot_company_{10m}.tariff_plan_observe
 */
class Struct_Db_PivotCompany_TariffPlanObserve {

	/**
	 * Конструктор
	 *
	 * @param int    $space_id
	 * @param int    $observe_at
	 * @param int    $report_after
	 * @param string $last_error_logs
	 * @param int    $created_at
	 * @param int    $updated_at
	 */
	public function __construct(
		public int    $space_id,
		public int    $observe_at,
		public int    $report_after,
		public string $last_error_logs,
		public int    $created_at,
		public int    $updated_at,
	) {
	}

}