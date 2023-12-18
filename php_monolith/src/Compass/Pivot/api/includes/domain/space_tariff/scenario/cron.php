<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Сценарии для кронов.
 */
class Domain_SpaceTariff_Scenario_Cron {

	/**
	 * Выполняет обзерв состояния кронов
	 */
	public static function cronObserve():void {

		if (isTestServer() || ServerProvider::isOnPremise()) {
			return;
		}

		// есть ли платные пространства с ошибками
		[$is_exist_failed_observe, $failed_observe_shard] = self::_isExistFailedObserve();

		// есть ли платные пространства, которые не проверялись более 1 часа и 5 минут
		[$is_exist_stuck_observe, $stuck_observe_shard] = self::_isExistStuckObserve();

		// есть ли задачи, которые завершились с ошибкой
		[$is_exist_failed_task, $failed_task_shard] = self::_isExistFailedTask();

		// есть ли задачи, которые висят в очереди больше 10 секунд
		[$is_exist_stuck_task, $stuck_task_shard] = self::_isExistStuckTask();

		// формируем сообщение для алерта в чат Compass
		$message = self::_getMessageTextForAlert(
			$is_exist_failed_observe, $is_exist_stuck_observe, $failed_observe_shard, $stuck_observe_shard,
			$is_exist_failed_task, $is_exist_stuck_task, $failed_task_shard, $stuck_task_shard
		);

		// отправляем в Compass
		Domain_SpaceTariff_Entity_Alert::send($message);
	}

	/**
	 * есть ли платные пространства с ошибками
	 */
	protected static function _isExistFailedObserve():array {

		$observe_list = Domain_Space_Action_Tariff_GetFailedObserveList::do();

		if (count($observe_list) < 1) {
			return [false, ""];
		}

		$shard_key_list = self::_getObserveShardKeyList($observe_list);

		return [true, $shard_key_list];
	}

	/**
	 * есть ли платные пространства, которые не проверялись более 1 часа и 5 минут
	 */
	protected static function _isExistStuckObserve():array {

		$observe_list = Domain_Space_Action_Tariff_GetStuckObserveList::do();

		if (count($observe_list) < 1) {
			return [false, ""];
		}

		$shard_key_list = self::_getObserveShardKeyList($observe_list);

		return [true, $shard_key_list];
	}

	/**
	 * есть ли задачи, которые завершились с ошибкой
	 */
	protected static function _isExistStuckTask():array {

		$task_list = Domain_Space_Action_Tariff_GetStuckTaskList::do();

		if (count($task_list) < 1) {
			return [false, ""];
		}

		$shard_key_list = self::_getTaskShardKeyList($task_list);

		return [true, $shard_key_list];
	}

	/**
	 * есть ли задачи, которые висят в очереди больше 10 секунд
	 */
	protected static function _isExistFailedTask():array {

		$task_list = Domain_Space_Action_Tariff_GetFailedTaskList::do();

		if (count($task_list) < 1) {
			return [false, ""];
		}

		$shard_key_list = self::_getTaskShardKeyList($task_list);

		return [true, $shard_key_list];
	}

	/**
	 * формируем сообщение для алерта в Compass
	 * @long
	 */
	protected static function _getMessageTextForAlert(bool $is_exist_failed_observe, bool $is_exist_stuck_observe, string $failed_observe_shard, string $stuck_observe_shard,
									  bool $is_exist_failed_task, bool $is_exist_stuck_task, string $failed_task_shard, string $stuck_task_shard):string {

		// получаем общее состояние по обсервам и выполненным таскам
		$observe_cron_state_is_ok = !$is_exist_failed_observe && !$is_exist_stuck_observe;
		$task_cron_state_is_ok    = !$is_exist_failed_task && !$is_exist_stuck_task;

		$state_message = $observe_cron_state_is_ok && $task_cron_state_is_ok ? "*Общий статус* - ++OK++" : ":bangbang:\n*Общий статус* - --Error--";

		// формируем текст для обсервов тарифов
		$observe_cron_message = "pivot_company_{m}.tariff_plan_observer - " . ($observe_cron_state_is_ok ? "++OK++" : "--Error--");
		$observer_cron_failed = "Отсутствие ошибок - " . ($is_exist_failed_observe ? "--Error ({$failed_observe_shard})--" : "++OK++");
		$observer_cron_stuck  = "Отсутствие непроверенных пространств - " . ($is_exist_stuck_observe ? "--Error ({$stuck_observe_shard})--" : "++OK++");

		// формируем текст для выполняемых тасков
		$task_cron_message = "pivot_company_{m}.tariff_plan_task - " . ($task_cron_state_is_ok ? "++OK++" : "--Error--");
		$task_cron_failed  = "Отсутствие ошибок - " . ($is_exist_failed_task ? "--Error ({$failed_task_shard})--" : "++OK++");
		$task_cron_stuck   = "Отсутствие долговыполняющихся задач - " . ($is_exist_stuck_task ? "--Error ({$stuck_task_shard})--" : "++OK++");

		$message = <<<EOL
{$state_message}

{$observe_cron_message}
{$observer_cron_failed}
{$observer_cron_stuck}

{$task_cron_message}
{$task_cron_failed}
{$task_cron_stuck}
EOL;

		return trim($message);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем список шардов для тасков
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanTask[] $task_list
	 *
	 * @return string
	 */
	protected static function _getTaskShardKeyList(array $task_list):string {

		$shard_key_list  = "";
		$shard_key_array = [];

		foreach ($task_list as $task) {

			$shard_key = ceil($task->space_id / 10000000) . "0m";
			if (in_array($shard_key, $shard_key_array)) {
				continue;
			}

			$shard_key_array[] = $shard_key;
			$shard_key_list    .= $shard_key . ", ";
		}

		$shard_key_list = mb_substr($shard_key_list, 0, -2);

		return $shard_key_list;
	}

	/**
	 * получаем список шардов для обсервов
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanObserve[] $observe_list
	 *
	 * @return string
	 */
	protected static function _getObserveShardKeyList(array $observe_list):string {

		$shard_key_list  = "";
		$shard_key_array = [];

		foreach ($observe_list as $observe) {

			$shard_key = ceil($observe->space_id / 10000000) . "0m";
			if (in_array($shard_key, $shard_key_array)) {
				continue;
			}

			$shard_key_array[] = $shard_key;
			$shard_key_list    .= $shard_key . ", ";
		}

		$shard_key_list = mb_substr($shard_key_list, 0, -2);

		return $shard_key_list;
	}
}