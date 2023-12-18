<?php

namespace Compass\Company;

/**
 * Базовый класс для добавления требовательности в карточку сотрудника компании
 */
class Domain_EmployeeCard_Action_Exactingness_Add {

	/**
	 * Выполняем действие добавления требовательности в карточку сотрудника компании
	 *
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	public static function do(int $user_id, array $user_id_list, int $created_at = null):array {

		if (is_null($created_at)) {
			$created_at = time();
		}

		// для каждого пользователя, к которому проявили Требовательность, добавляем эту Требовательность в базу
		$exactingness_id_list            = [];
		$exactingness_id_list_by_user_id = [];
		foreach ($user_id_list as $v) {

			$exactingness_obj       = Type_User_Card_Exactingness::add($v, $user_id, Type_User_Card_Exactingness::EXACTINGNESS_TYPE_DEFAULT, $created_at);
			$exactingness_id_list[] = $exactingness_obj->exactingness_id;

			$exactingness_id_list_by_user_id[$v] = $exactingness_obj->exactingness_id;

			// инкрементим количество проявленных требовательностей в рейтинге
			Gateway_Bus_Company_Rating::inc(Domain_Rating_Entity_Rating::EXACTINGNESS, $user_id);
		}

		// инкрементим количество набранных пользователем требовательностей за этот месяц
		$month_start_at = monthStart($created_at);
		$week_start_at  = weekStart($created_at);
		Type_User_Card_MonthPlan::incUserValue($user_id, Type_User_Card_MonthPlan::MONTH_PLAN_EXACTINGNESS_TYPE, $month_start_at, count($exactingness_id_list));

		$week_count  = Gateway_Db_CompanyMember_UsercardExactingnessList::getCountByTime($user_id, $week_start_at);
		$month_count = Gateway_Db_CompanyMember_UsercardExactingnessList::getCountByTime($user_id, $month_start_at);

		return [$exactingness_id_list_by_user_id, $week_count, $month_count];
	}
}
