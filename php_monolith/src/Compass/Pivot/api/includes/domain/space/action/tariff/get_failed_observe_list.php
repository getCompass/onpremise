<?php

namespace Compass\Pivot;

/**
 * Получить список застрявших задач в обсервере
 */
class Domain_Space_Action_Tariff_GetFailedObserveList {

	protected const _MAX_COUNT = 10000; // в принципе таких задач быть не должно

	/**
	 * Выполняем действие
	 *
	 * @param int $shard_company_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do():array {

		$sharding_key_list = Gateway_Db_PivotCompany_Main::getExistingShardList();
		$observe_list      = [];

		foreach ($sharding_key_list as $sharding_key) {

			$observe_list = array_merge($observe_list, Gateway_Db_PivotCompany_TariffPlanObserve::getByReportAfter(
				$sharding_key, time(), self::_MAX_COUNT));
		}
		return $observe_list;
	}
}