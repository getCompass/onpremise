<?php

namespace Compass\Pivot;

/**
 * Получить список проваленных задач
 */
class Domain_Space_Action_Tariff_GetAverageQueueTime {

	/**
	 * Выполняем действие
	 *
	 * @param int $start_time
	 * @param int $end_time
	 *
	 * @return int
	 */
	public static function do(int $start_time, int $end_time):int {

		$sharding_key_list = Gateway_Db_PivotCompany_Main::getExistingShardList();
		$avg_time_list     = [];

		foreach ($sharding_key_list as $sharding_key) {
			$avg_time_list[] = Gateway_Db_PivotCompany_TariffPlanTaskHistory::getAverageInQueueTime($sharding_key, $start_time, $end_time);
		}

		return (int) (array_sum($avg_time_list) / count($avg_time_list));
	}
}