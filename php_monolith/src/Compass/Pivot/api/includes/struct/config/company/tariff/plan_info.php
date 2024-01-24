<?php

namespace Compass\Pivot;

/**
 * Структура информации о тарифных планах
 */
class Struct_Config_Company_Tariff_PlanInfo {

	/**
	 * Struct_Config_Company_Tariff_PlanInfo constructor.
	 *
	 */
	public function __construct(
		public \Tariff\Plan\MemberCount\SaveData $member_count,
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
			new \Tariff\Plan\MemberCount\SaveData(
				$config["member_count"]["plan_id"],
				$config["member_count"]["valid_till"],
				$config["member_count"]["active_till"],
				$config["member_count"]["free_active_till"],
				$config["member_count"]["option_list"],
			)
		);
	}
}
