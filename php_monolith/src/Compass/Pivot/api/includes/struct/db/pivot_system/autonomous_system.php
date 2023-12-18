<?php

namespace Compass\Pivot;

/**
 * Структура записи таблицы «autonomous_system».
 */
class Struct_Db_PivotSystem_AutonomousSystem {

	public int    $ip_range_start;
	public int    $ip_range_end;
	public int    $code;
	public string $country_code;
	public string $name;

	/**
	 * Constructor.
	 */
	public function __construct(int $ip_range_start, int $ip_range_end, int $code, string $country_code, string $name) {

		$this->ip_range_start = $ip_range_start;
		$this->ip_range_end   = $ip_range_end;
		$this->code           = $code;
		$this->country_code   = $country_code;
		$this->name           = $name;
	}
}