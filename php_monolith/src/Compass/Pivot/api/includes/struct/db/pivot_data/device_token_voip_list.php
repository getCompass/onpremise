<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_data.device_token_voip_list_{0-f}
 */
class Struct_Db_PivotData_DeviceTokenVoipList {

	public function __construct(
		public string $token_hash,
		public int    $user_id,
		public int    $created_at,
		public int    $updated_at,
		public string $device_id
	) {
	}
}