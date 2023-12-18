<?php

namespace Compass\Pivot;

/**
 * Class Struct_Db_PivotData_CheckpointCompany
 */
class Struct_Db_PivotData_CheckpointCompany {

	public int $list_type;
	public int $company_id;
	public int $expires_at;

	/**
	 * Struct_Db_PivotData_CheckpointCompany constructor.
	 *
	 */
	public function __construct(int $list_type, int $company_id, int $expires_at) {

		$this->list_type  = $list_type;
		$this->company_id = $company_id;
		$this->expires_at = $expires_at;
	}
}