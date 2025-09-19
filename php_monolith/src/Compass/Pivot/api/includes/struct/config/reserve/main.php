<?php

namespace Compass\Pivot;

/**
 * Структура конфига резервных серверов
 */
class Struct_Config_Reserve_Main {

	/**
	 * Struct_Config_Reserve_Main constructor.
	 *
	 */
	public function __construct(
		public string $host_ip,
		public array  $company_list,
	) {
	}

	public static function fromConfig(array $config):self {

		return new self(
			$config["host_ip"],
			$config["company_list"],
		);
	}
}
