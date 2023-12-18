<?php

namespace Compass\Pivot;

/**
 * Структура для портов mysql мира
 */
class Struct_Db_PivotSystem_CompanyTask {

	/**
	 * Struct_Db_PivotSystem_CompanyTask constructor.
	 *
	 */
	public function __construct(
		public int $company_id,
		public int $task_type,
		public int $need_work,
		public int $created_at,
		public int $updated_at
	) {
	}
}