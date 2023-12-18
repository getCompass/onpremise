<?php

namespace Compass\Pivot;

/**
 * Крон для выполнения задач, связанных с тарифами в пространстве
 */
class Cron_Company_TariffPlanTask extends \Cron_Default {

	protected const _MAX_TASK_LIMIT = 1000;

	public function __construct(array $config = []) {

		parent::__construct($config);
	}

	/**
	 * Выполняем задачу
	 */
	public function work():void {

		$sharding_key_list = Gateway_Db_PivotCompany_Main::getExistingShardList();

		// для каждого шарда
		foreach ($sharding_key_list as $sharding_key) {

			// получим задачи на обработку
			$task_list = Gateway_Db_PivotCompany_TariffPlanTask::getForWork($sharding_key, time(), self::_MAX_TASK_LIMIT);

			foreach ($task_list as $task) {

				// выполняем задачу
				Domain_Space_Entity_Tariff_PlanTask::exec($task);
			}
		}
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "pivot_" . parent::_resolveBotName();
	}
}