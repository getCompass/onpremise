<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_data.device_list_{0-f}
 */
class Struct_Db_PivotData_Device {

	public string $device_id;
	public int    $user_id;
	public int    $created_at;
	public int    $updated_at;
	public array  $extra;

	/**
	 * Struct_Db_PivotData_Device constructor.
	 *
	 */
	public function __construct(
		string $device_id,
		int    $user_id,
		int    $created_at,
		int    $updated_at,
		array  $extra
	) {

		$this->device_id  = $device_id;
		$this->user_id    = $user_id;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->extra      = $extra;
	}
}
