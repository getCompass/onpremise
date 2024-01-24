<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Базовый класс для отправки рейтинга за последний месяц
 */
class Domain_Rating_Action_SendRatingForLastMonth {

	/**
	 * отправляем статистику за последний месяц всем пользователям
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $year, int $month):bool {

		// получаем timestamp время, когда месяц начался
		$month_start_at = Type_Rating_Helper::monthBeginAtByYearAndMonth($year, $month);

		// получаем timestamp время, когда месяц заканчивается
		$month_end_at = Type_Rating_Helper::monthEndAtByYearAndMonth($year, $month);

		// формируем data для последующей отправки сообщения
		$data = [
			"period_start_date" => monthBeginAtByYearAndMonth($year, $month),
			"period_end_date"   => monthEndAtByYearAndMonth($year, $month),
		];

		$respect_rating      = Gateway_Bus_Company_Rating::getByMonth("respect", $month_start_at, $month_end_at, 0, 10);
		$exactingness_rating = Gateway_Bus_Company_Rating::getByMonth("exactingness", $month_start_at, $month_end_at, 0, 10);

		// не шлем сообщение если не было добавлено требовательности/спасибо
		if ($respect_rating->count < 1 && $exactingness_rating->count < 1) {
			return false;
		}

		// формируем data для ивента, чтобы отправить сообщение от бота
		$data["metric_count_item_list"][] = ["metric_type" => "respect", "count" => $respect_rating->count];
		$data["metric_count_item_list"][] = ["metric_type" => "exactingness", "count" => $exactingness_rating->count];

		// достаем имя компании
		$config               = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME);
		$company_name         = $config["value"];
		$data["company_name"] = $company_name;

		// достаем чат в который шлем сообщение
		$conversation_config      = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME);
		$data["conversation_map"] = $conversation_config["value"];

		// пушим событие о фиксации общего числе действий с карточкой
		Gateway_Event_Dispatcher::dispatch(Type_Event_CompanyRating_EmployeeMetricTotalFixed::create(...$data), true);

		return true;
	}
}