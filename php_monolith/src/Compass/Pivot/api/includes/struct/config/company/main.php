<?php

namespace Compass\Pivot;

/**
 * Структура company_config
 */
class Struct_Config_Company_Main {

	/**
	 * Struct_Config_Company_Main constructor.
	 *
	 */
	public function __construct(
		public int                           $status,
		public string                        $domino_id,
		public ?Struct_Config_Company_Mysql  $mysql = null,
		public ?Struct_Config_Company_Tariff $tariff = null
	) {
	}

	public function setMysql(?Struct_Config_Company_Mysql $mysql):self {

		$this->mysql = $mysql;
		return $this;
	}

	public function setTariff(?Struct_Config_Company_Tariff $tariff):self {

		$this->tariff = $tariff;
		return $this;
	}

	public static function fromConfig(array $config):self {

		return new self(
			$config["status"],
			$config["domino_id"],
			Struct_Config_Company_Mysql::fromConfig($config["mysql"]),
			Struct_Config_Company_Tariff::fromConfig($config["tariff"])
		);
	}
}
