<?php

namespace Compass\Pivot;

/**
 * Структура mysql в конфиге компании
 */
class Struct_Config_Company_Tariff {

	/**
	 * Struct_Config_CompanyMysql constructor.
	 *
	 */
	public function __construct(
		public Struct_Config_Company_Tariff_PlanInfo $plan_info,
	) {
	}

	/**
	 * Сформировать из конфига
	 *
	 * @param array $config
	 *
	 * @return static
	 */
	public static function fromConfig(array $config):self {

		return new self(
			Struct_Config_Company_Tariff_PlanInfo::fromConfig(
				$config["plan_info"]
			));
	}
}
