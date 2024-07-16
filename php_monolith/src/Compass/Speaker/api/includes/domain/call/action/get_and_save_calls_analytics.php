<?php

namespace Compass\Speaker;

/**
 * Action для получения и сохранения аналитики по звонкам
 */
class Domain_Call_Action_GetAndSaveCallsAnalytics {

	public const    INTERVAL         = 3; // интервал сбора аналитики
	protected const _MAX_ERROR_COUNT = 5; // макс кол-во ошибок

	/**
	 * выполняем действие
	 * @long
	 */
	public static function do():bool {

		if (!ANALYTICS_IS_ENABLED) {
			return false;
		}

		// собираем записи с таблицы analytic_queue
		$task_list = Gateway_Db_CompanyCall_AnalyticQueue::getTaskListWhichNeedWork();

		// если задач вообще нет, то останавливаем выполнение
		if (count($task_list) < 1) {
			return false;
		}

		$incorrect_task_id_list        = [];
		$need_remove_task_id_list      = [];
		$update_need_work_task_id_list = [];

		// для каждой задачи
		foreach ($task_list as $task) {

			// если задаче ещё рано выполняться
			if ($task["need_work"] > time()) {
				continue;
			}

			// если задача поймала максимальное количество ошибок
			if ($task["error_count"] >= self::_MAX_ERROR_COUNT) {

				Domain_Call_Action_GetAndSaveAnalyticsForUser::do((int) $task["task_id"], (int) $task["user_id"], $task["call_map"], false);
				$need_remove_task_id_list[] = (int) $task["task_id"];
				continue;
			}

			// собираем аналитику для пользователя в звонке
			try {

				Domain_Call_Action_GetAndSaveAnalyticsForUser::do((int) $task["task_id"], (int) $task["user_id"], $task["call_map"]);
				$update_need_work_task_id_list[] = (int) $task["task_id"];
			} catch (\cs_RowIsEmpty) {

				// если ошибка, то добавляем таску в список некорректных, кому инкрементнём кол-во ошибок
				$incorrect_task_id_list[] = (int) $task["task_id"];
			}
		}

		// обновляем need_work для записей
		self::_updateNeedWorkTasks($update_need_work_task_id_list);

		// инкрементим количество ошибок для некорректных задач
		self::_updateIncorrectTasks($incorrect_task_id_list);

		// удаляем те задачи, которые набрали максимальное количество ошибок
		self::_removeErrorTasks($need_remove_task_id_list);

		return true;
	}

	/**
	 * обновляем need_work для полученного списка задач
	 */
	protected static function _updateNeedWorkTasks(array $task_id_list):void {

		if (count($task_id_list) < 1) {
			return;
		}

		$set = ["need_work" => time() + self::INTERVAL];
		Gateway_Db_CompanyCall_AnalyticQueue::setList($task_id_list, $set);
	}

	/**
	 * обновляем для некорректных задач кол-во ошибок и need_work следующего выполнения
	 */
	protected static function _updateIncorrectTasks(array $task_id_list):void {

		if (count($task_id_list) < 1) {
			return;
		}

		$set = [
			"error_count" => "error_count + 1",
			"need_work"   => time() + self::INTERVAL,
		];

		Gateway_Db_CompanyCall_AnalyticQueue::setList($task_id_list, $set);
	}

	/**
	 * удаляем задачи, набравшие больше всего ошибок
	 */
	protected static function _removeErrorTasks(array $task_id_list):void {

		if (count($task_id_list) < 1) {
			return;
		}

		Gateway_Db_CompanyCall_AnalyticQueue::deleteList($task_id_list);
	}
}