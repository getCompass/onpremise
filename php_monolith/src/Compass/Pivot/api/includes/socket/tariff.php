<?php

namespace Compass\Pivot;

/**
 * Контроллер для тарифов пространств
 */
class Socket_Tariff extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"getFailedTaskList",
		"getStuckTaskList",
		"getFailedTaskHistory",
		"getFailedObserveList",
		"getStuckObserveList",
		"getAverageQueueTime",
		"increaseMemberCountLimit",
	];

	/**
	 * Получить заваленные задачи
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function getFailedTaskList():array {

		$task_list = Domain_Space_Scenario_Tariff_Socket::getFailedTaskList();

		return $this->ok([
			"task_list" => (array) $task_list,
		]);
	}

	/**
	 * Получить зависшие задачи
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function getStuckTaskList():array {

		$task_list = Domain_Space_Scenario_Tariff_Socket::getStuckTaskList();

		return $this->ok([
			"task_list" => (array) $task_list,
		]);
	}

	/**
	 * Получить заваленные обсервы
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function getFailedObserveList():array {

		$task_list = Domain_Space_Scenario_Tariff_Socket::getFailedObserveList();

		return $this->ok([
			"observe_list" => (array) $task_list,
		]);
	}

	/**
	 * Получить зависшие обсервы
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function getStuckObserveList():array {

		$task_list = Domain_Space_Scenario_Tariff_Socket::getStuckObserveList();

		return $this->ok([
			"observe_list" => (array) $task_list,
		]);
	}

	/**
	 * Получить среднее время ввыполнения задач
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getAverageQueueTime():array {

		$start_time = $this->post(\Formatter::TYPE_INT, "start_time");
		$end_time   = $this->post(\Formatter::TYPE_INT, "end_time");

		$avg_time = Domain_Space_Scenario_Tariff_Socket::getAverageQueueTime($start_time, $end_time);

		return $this->ok([
			"average_queue_time" => (int) $avg_time,
		]);
	}

	/**
	 * Получить историю заваленных задач
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getFailedTaskHistory():array {

		$start_time = $this->post(\Formatter::TYPE_INT, "start_time");
		$end_time   = $this->post(\Formatter::TYPE_INT, "end_time");

		$task_list = Domain_Space_Scenario_Tariff_Socket::getFailedTaskHistory($start_time, $end_time);

		return $this->ok([
			"task_list" => (array) $task_list,
		]);
	}

	/**
	 * Проверяем, можно ли добавить нового пользователя
	 *
	 * @return array
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_RowIsEmpty
	 */
	public function increaseMemberCountLimit():array {

		[$can_increase, $is_trial_activated] = Domain_Space_Scenario_Tariff_Socket::increaseMemberCountLimit($this->user_id, $this->company_id);

		return $this->ok([
			"can_increase"       => (int) $can_increase,
			"is_trial_activated" => (int) $is_trial_activated,
		]);
	}
}