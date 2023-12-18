<?php

namespace Compass\Pivot;

/**
 * Класс структуры для записей базы pivot_company_{10m}.tariff_plan_task
 */
class Struct_Db_PivotCompany_TariffPlanTask {

	/**
	 * Конструктор
	 *
	 * @param int    $id
	 * @param int    $space_id
	 * @param int    $type
	 * @param int    $status
	 * @param int    $need_work
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $logs
	 * @param array  $extra
	 */
	public function __construct(
		public int    $id,
		public int    $space_id,
		public int    $type,
		public int    $status,
		public int    $need_work,
		public int    $created_at,
		public int    $updated_at,
		public string $logs,
		public array  $extra,
	) {
	}

}