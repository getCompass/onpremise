<?php

namespace Compass\Pivot;

/**
 * Класс структуры для записей базы pivot_company_service.port_registry_{domino_id}
 */
class Struct_Db_PivotCompanyService_PortRegistry {

	/**
	 * Конструктор
	 *
	 * @param int   $port
	 * @param int   $status
	 * @param int   $type
	 * @param int   $locked_till
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param int   $company_id
	 * @param array $extra
	 */
	public function __construct(
		public int   $port,
		public int   $status,
		public int   $type,
		public int   $locked_till,
		public int   $created_at,
		public int   $updated_at,
		public int   $company_id,
		public array $extra,
	) {
	}

}