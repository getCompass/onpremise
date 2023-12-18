<?php

namespace Compass\Company;

/**
 * Класс для автоустановки плана на месяц в обсервере
 */
class Type_User_Observer_SetMonthPlan extends Type_User_Observer_Default {

	/**
	 * Создает дополнительные данные для генератора задачи.
	 * DependencyInjection штука, чтобы генератор можно было затестить в любой момент.
	 */
	public static function provideJobExtra(int $user_id, int $job_type):array {

		return [
			"user_id"                => $user_id,                                                // наш пользователь
			"job_type"               => $job_type,                                               // тип выполняемой задачи
			"action_time"            => time(),                                                  // время совершения действия
			"next_time_if_first_job" => strtotime("last day of this month 12:00"),               // когда нужно исполнить действие для первого выполнения задачи
			"next_time"              => strtotime("last day of next month 12:00"),               // когда нужно исполнить действие в следующий раз
			"month_start_at"         => monthStart(strtotime("first day of next month 12:00")),  // временная метка начала следующего месяца
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
		$job_type = Type_User_Observer::JOB_TYPE_SET_NEXT_MONTH_PLAN;

		// если время не пришло, то просто выходим
		if ($observer_data[$job_type]["need_work"] > $job_extra["action_time"]) {
			return [];
		}

		// генерируем данные для выполнения задачи
		$job_data = [
			"user_id"        => $job_extra["user_id"],
			"next_time"      => intval($job_extra["next_time"]),
			"observer_data"  => $observer_data,
			"month_start_at" => $job_extra["month_start_at"],
		];

		return [
			$job_type => $job_data,
		];
	}

	/**
	 * Выполняем задачу
	 */
	public static function doJob(array $job_data):int {

		$user_id = $job_data["user_id"];

		// проверяем, что планы на следующий месяц установлены
		$next_month_start_at      = $job_data["month_start_at"];
		$next_month_plan_obj_list = Type_User_Card_MonthPlan::getAllType($user_id, $next_month_start_at);
		$is_exist_next_month      = true;
		foreach ($next_month_plan_obj_list as $plan_obj) {
			$is_exist_next_month = $is_exist_next_month && $plan_obj->created_at == $job_data["month_start_at"];
		}

		// если планы уже созданы для каждой доступной сущности
		if ($is_exist_next_month && count($next_month_plan_obj_list) == count(Type_User_Card_MonthPlan::ALLOW_MONTH_PLAN_TYPE_LIST)) {
			return $job_data["next_time"];
		}

		// получаем планы текущего месяца
		$month_plan_obj_list = Type_User_Card_MonthPlan::getAllType($user_id, monthStart());

		// если нет планов на текущий месяц их нужно создать
		if (count($month_plan_obj_list) < count(Type_User_Card_MonthPlan::ALLOW_MONTH_PLAN_TYPE_LIST)) {

			// проверяем каждый тип плана на существование и создаем в случае отсутствия
			foreach (Type_User_Card_MonthPlan::ALLOW_MONTH_PLAN_TYPE_LIST as $plan_type) {
				self::_createIfNotExist($month_plan_obj_list, $user_id, $plan_type);
			}

			// получаем планы и обновляем переменную
			$month_plan_obj_list = Type_User_Card_MonthPlan::getAllType($user_id, monthStart());
		}

		// для каждой сущности плана на месяц
		foreach ($month_plan_obj_list as $plan_obj) {

			// создаем/перезаписываем (не перезаписываем поле current_value плана) план на следующий месяц
			Type_User_Card_MonthPlan::insertOrUpdate($user_id, $plan_obj->type, $job_data["month_start_at"], $plan_obj->plan_value);
		}

		// устанавливаем следующее выполнение задачи
		return $job_data["next_time"];
	}

	/**
	 * Проверить существует ли план переданного типа на текущий месяц, если нет - создаем
	 *
	 * @param array $month_plan_obj_list
	 * @param int   $user_id
	 * @param int   $plan_type
	 *
	 * @return void
	 */
	protected static function _createIfNotExist(array $month_plan_obj_list, int $user_id, int $plan_type):void {

		// проверяем если план такого типа уже существует - выходим из метода
		foreach ($month_plan_obj_list as $plan_obj) {

			if ($plan_obj->type == $plan_type) {
				return;
			}
		}

		// создаем план всегда с нулевыми значениями, так как если бы он был не нулевой то он бы существовал
		Type_User_Card_MonthPlan::insertOrUpdate($user_id, $plan_type, monthStart(), 0);
	}

	/**
	 * Возвращает время следующего выполнения задачи
	 */
	public static function getNextWorkTime(array $job_extra, bool $is_need_of_current_month = true):int {

		if ($is_need_of_current_month) {
			return intval($job_extra["next_time_if_first_job"]);
		}

		return intval($job_extra["next_time"]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------
}