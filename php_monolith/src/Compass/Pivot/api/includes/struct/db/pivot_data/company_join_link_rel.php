<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_data.join_link_rel
 */
class Struct_Db_PivotData_CompanyJoinLinkRel {

	public string $join_link_uniq;
	public int    $company_id;
	public int    $status_alias;
	public int    $created_at;
	public int    $updated_at;

	/**
	 * Struct_Db_PivotData_CompanyJoinLinkRel constructor.
	 *
	 */
	public function __construct(string $join_link_uniq, int $company_id, int $status_alias, int $created_at, int $updated_at) {

		$this->join_link_uniq = $join_link_uniq;
		$this->company_id     = $company_id;
		$this->status_alias   = $status_alias;
		$this->created_at     = $created_at;
		$this->updated_at     = $updated_at;
	}
}