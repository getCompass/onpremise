<?php

namespace Compass\Pivot;

/**
 * Структура mysql в конфиге компании
 */
class Struct_Config_Company_Mysql {

	/**
	 * Struct_Config_CompanyMysql constructor.
	 *
	 */
	public function __construct(
		public string $host,
		public int    $port,
		public string $user,
		public string $pass,
	) {
	}

	public static function fromConfig(array $config):self {

		return new self(
			$config["host"],
			$config["port"],
			$config["user"],
			$config["pass"]
		);
	}
}
