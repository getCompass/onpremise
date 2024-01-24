<?php

namespace Compass\Pivot;

/**
 * Получить историю проваленных задач
 */
class Domain_Space_Action_Tariff_GetFailedTaskHistory {

	/**
	 * Выполняем действие
	 *
	 * @param int $start_time
	 * @param int $end_time
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(int $start_time, int $end_time):array {

		$sharding_key_list = Gateway_Db_PivotCompany_Main::getExistingShardList();
		$task_list         = [];

		foreach ($sharding_key_list as $sharding_key) {

			$task_list = array_merge($task_list, Gateway_Db_PivotCompany_TariffPlanTaskHistory::getHistory(
				$sharding_key, Domain_Space_Entity_Tariff_PlanTask::TASK_STATUS_ERROR, $start_time, $end_time));
		}

		return $task_list;
	}
}