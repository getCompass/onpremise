<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_data.company_join_link_user_rel
 */
class Struct_Db_PivotData_CompanyJoinLinkUserRel {

	public string $join_link_uniq;
	public int    $user_id;
	public int    $company_id;
	public int    $entry_id;
	public int    $status;
	public int    $created_at;
	public int    $updated_at;

	/**
	 * Struct_Db_PivotData_CompanyJoinLinkUserRel constructor.
	 *
	 */
	public function __construct(string $join_link_uniq, int $user_id, int $company_id, int $entry_id, int $status, int $created_at, int $updated_at) {

		$this->join_link_uniq = $join_link_uniq;
		$this->user_id        = $user_id;
		$this->company_id     = $company_id;
		$this->entry_id       = $entry_id;
		$this->status         = $status;
		$this->created_at     = $created_at;
		$this->updated_at     = $updated_at;
	}
}