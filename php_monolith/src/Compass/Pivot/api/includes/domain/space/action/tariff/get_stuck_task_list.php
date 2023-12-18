<?php

namespace Compass\Pivot;

/**
 * Получить список проваленных задач
 */
class Domain_Space_Action_Tariff_GetStuckTaskList {

	protected const _MAX_COUNT = 10000; // в принципе таких задач быть не должно

	/**
	 * Выполняем действие
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do():array {

		$sharding_key_list = Gateway_Db_PivotCompany_Main::getExistingShardList();
		$task_list         = [];

		foreach ($sharding_key_list as $sharding_key) {

			$task_list = array_merge($task_list, Gateway_Db_PivotCompany_TariffPlanTask::getForWork(
				$sharding_key, time() - 10, self::_MAX_COUNT));
		}
		return $task_list;
	}
}