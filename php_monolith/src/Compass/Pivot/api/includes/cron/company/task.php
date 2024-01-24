<?php

namespace Compass\Pivot;

/**
 * Крон для выполнения задачи в компании
 */
class Cron_Company_Task extends \Cron_Default {

	protected int $task_type = Domain_Company_Entity_CronCompanyTask::TYPE_EXIT;

	public function __construct(array $config = []) {

		parent::__construct($config);
		global $argv;

		if (in_array("task-type-exit", $argv)) {
			$this->task_type = Domain_Company_Entity_CronCompanyTask::TYPE_EXIT;
		}
	}

	/**
	 * Выполняем задачу
	 */
	public function work():void {

		// получим задачи на обработку
		$tasks = Gateway_Db_PivotData_CompanyTaskQueue::getTasksNeedComplete($this->task_type, Domain_Company_Entity_CronCompanyTask::TASK_STATUS_LIST_NEED_CHECK);

		foreach ($tasks as $task) {

			// выполняем задачу
			Domain_Company_Entity_CronCompanyTask::run($task);
		}
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "pivot_" . parent::_resolveBotName();
	}
}