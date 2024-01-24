<?php

namespace Compass\Company;

/**
 * Класс для автоматической отправки рабочих часов в обсервере
 */
class Type_User_Observer_AutoCommitWorkedHours extends Type_User_Observer_Default {

	/**
	 * Создает дополнительные данные для генератора задачи.
	 * DependencyInjection штука, чтобы генератор можно было затестить в любой момент.
	 *
	 * @param int $user_id
	 * @param int $job_type
	 *
	 * @return array
	 */
	public static function provideJobExtra(int $user_id, int $job_type):array {

		return [
			"user_id"               => $user_id,                        // наш пользователь
			"job_type"              => $job_type,                       // тип выполняемой задачи
			"set_worked_hours"      => 8,                               // сколько часов устанавливаем для автофиксации
			"action_time"           => time(),                          // время совершения действия
			"next_time"             => strtotime("next day 04:00"),     // когда нужно исполнить действие в следующий раз
			"next_time_if_saturday" => strtotime("next Tuesday 04:00"), // когда нужно исполнить действие в следующий раз (если сегодня суббота)
		];
	}

	/**
	 * Генерирует задачи, которые нужно взять на исполнение
	 *
	 * @param array $observer_data обсервер data
	 * @param array $job_extra     экстра для генерации задачи
	 */
	public static function provideJobList(array $observer_data, array $job_extra):array {

		// тип выполняемой задачи
		$job_type = $job_extra["job_type"];

		// если время не пришло, то просто выходим
		if ($observer_data[$job_type]["need_work"] > $job_extra["action_time"]) {
			return [];
		}

		// генерируем данные для выполнения задачи
		$job_data = [
			"user_id"          => $job_extra["user_id"],
			"set_worked_hours" => $job_extra["set_worked_hours"],
			"next_time"        => self::getNextWorkTime($job_extra),
			"observer_data"    => $observer_data,
		];

		return [
			$job_type => $job_data,
		];
	}

	/**
	 * Выполняем задачу
	 *
	 * @param array $job_data
	 *
	 * @return int
	 * @throws \queryException
	 */
	public static function doJob(array $job_data):int {

		$is_extended_employee_card_enabled = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY)["value"];

		// если включена базовая карточка, пропускаем задачу
		if ($is_extended_employee_card_enabled === 0) {
			return $job_data["next_time"];
		}

		$user_id = $job_data["user_id"];

		// проверяем, не зафиксировал ли уже рабочие часы пользователь
		// (т.к. крон выполняет автофиксацию на следующий день, то пробуем достать фиксацию за прошлый день)
		$day_start_at = strtotime("this day 00:00") - DAY1;

		try {
			$worked_hours_obj = Type_User_Card_WorkedHours::getOneByDayStartAt($user_id, $day_start_at);
		} catch (\cs_RowIsEmpty) {

			// если фиксация отсутствует, то начинаем автокоммитить рабочие часы
			self::_do($user_id, $job_data["set_worked_hours"], $job_data["next_time"]);
			return $job_data["next_time"];
		}

		// если фиксация удалена, то начинаем автокоммитить рабочие часы
		if ($worked_hours_obj->is_deleted == 1) {
			self::_do($user_id, $job_data["set_worked_hours"], $job_data["next_time"]);
		}

		return $job_data["next_time"];
	}

	/**
	 * автофиксируем рабочие часы пользователя
	 */
	protected static function _do(int $user_id, float $set_worked_hours, int $next_time):void {

		// время автофиксации строго из переменной
		$auto_commit_time_at = $next_time - DAY1;

		// автоматически фиксируем рабочие часы пользователю
		try {
			Gateway_Socket_Conversation::autoCommitWorkedHours([$user_id], $set_worked_hours, $auto_commit_time_at);
		} catch (\Exception) {

			Type_System_Admin::log("user_observer_fail", "Не смогли автозакоммитить рабочие часы для пользователя user_id = {$user_id}");
		}
	}

	/**
	 * Возвращает время следующего выполнения задачи
	 */
	public static function getNextWorkTime(array $job_extra):int {

		// если сегодня суббота, то следующее выполнение не на следующий день, а во вторник
		if (getWeekDayString() == "Saturday") {
			return intval($job_extra["next_time_if_saturday"]);
		}

		return intval($job_extra["next_time"]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------
}