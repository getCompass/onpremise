<?php

namespace Compass\Pivot;

/**
 * Структура для подозрительных ip
 */
class Struct_Db_PivotSystem_AntispamSuspectIp {

	/**
	 * Struct_Db_PivotSystem_AntispamSuspectIp constructor.
	 */
	public function __construct(
		public string $ip_address,
		public string $phone_code,
		public int    $created_at,
		public int    $expires_at,
		public int    $delayed_till,
	) {
	}
}