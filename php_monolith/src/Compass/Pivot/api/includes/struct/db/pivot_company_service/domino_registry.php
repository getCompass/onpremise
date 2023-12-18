<?php

namespace Compass\Pivot;

/**
 * Класс структуры для записей базы pivot_company_service.domino_registry
 */
class Struct_Db_PivotCompanyService_DominoRegistry {

	/**
	 * Конструктор
	 *
	 * @param string $domino_id
	 * @param string $code_host
	 * @param string $database_host
	 * @param int    $is_company_creating_allowed
	 * @param int    $hibernation_locked_until
	 * @param int    $tier
	 * @param int    $common_port_count
	 * @param int    $service_port_count
	 * @param int    $reserved_port_count
	 * @param int    $common_active_port_count
	 * @param int    $reserve_active_port_count
	 * @param int    $service_active_port_count
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param array  $extra
	 */
	public function __construct(
		public string $domino_id,
		public string $code_host,
		public string $database_host,
		public int    $is_company_creating_allowed,
		public int    $hibernation_locked_until,
		public int    $tier,
		public int    $common_port_count,
		public int    $service_port_count,
		public int    $reserved_port_count,
		public int    $common_active_port_count,
		public int    $reserve_active_port_count,
		public int    $service_active_port_count,
		public int    $created_at,
		public int    $updated_at,
		public array  $extra,
	) {
	}

}