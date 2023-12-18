<?php

namespace Compass\Pivot;

/**
 * Class Struct_Db_PivotData_CheckpointPhoneNumber
 */
class Struct_Db_PivotData_CheckpointPhoneNumber {

	public int    $list_type;
	public string $phone_number_hash;
	public int    $expires_at;

	/**
	 * Struct_Db_PivotData_CheckpointPhoneNumber constructor.
	 *
	 */
	public function __construct(int $list_type, string $phone_number_hash, int $expires_at) {

		$this->list_type         = $list_type;
		$this->phone_number_hash = $phone_number_hash;
		$this->expires_at        = $expires_at;
	}
}