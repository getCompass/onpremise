<?php

namespace Compass\Pivot;

/**
 * Класс структуры для записей базы pivot_company_service.relocation_history
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_PivotCompanyService_RelocationHistory {

	/**
	 * Конструктор структуры для записей базы pivot_company_service.relocation_history.
	 */
	public function __construct(
		public int    $relocation_id,
		public int    $is_success,
		public int    $created_at,
		public int    $updated_at,
		public int    $finished_at,
		public int    $company_id,
		public string $source_domino_id,
		public string $target_domino_id,
		public array  $extra,
	) {
	}
}