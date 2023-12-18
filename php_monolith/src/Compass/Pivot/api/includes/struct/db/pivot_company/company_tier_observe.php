<?php

namespace Compass\Pivot;

/**
 * Класс-структура для таблицы pivot_company_{10m}.company_tier_observe
 */
class Struct_Db_PivotCompany_CompanyTierObserve extends Struct_Default {

	public function __construct(
		public int    $company_id,
		public string $current_domino_tier,
		public string $expected_domino_tier,
		public int    $need_work,
		public int    $created_at,
		public int    $updated_at,
		public array  $extra,
	) {

	}
}
