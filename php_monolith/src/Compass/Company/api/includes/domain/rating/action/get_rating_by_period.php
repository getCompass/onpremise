<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Базовый класс для получения рейтинга по периодам
 */
class Domain_Rating_Action_GetRatingByPeriod {

	/**
	 * получение рейтинга по периодам
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @throws \busException
	 */
	public static function do(int $period_type, string $event, int $year, int $month, int $week, int $top_list_offset, int $top_list_count):Struct_Bus_Rating_General {

		switch ($period_type) {

			// за неделю
			case Domain_Rating_Entity_Rating::PERIOD_WEEK_TYPE:
			default:

				// получаем timestamp когда неделя начинается
				$from_date_at = Type_Rating_Helper::weekBeginAtByYearAndWeek($year, $week);

				// получаем timestamp когда неделя заканчивается
				$to_date_at = Type_Rating_Helper::weekEndAtByYearAndWeek($year, $week);
				$to_date_at = $to_date_at < $from_date_at ? $from_date_at : $to_date_at;

				$rating       = Gateway_Bus_Company_Rating::get($event, $from_date_at, $to_date_at, $top_list_offset, $top_list_count);
				$rating->year = $year;
				$rating->week = $week;
				return $rating;

			// за месяц
			case Domain_Rating_Entity_Rating::PERIOD_MONTH_TYPE:

				// получаем unix время когда месяц начался
				$month_start_at = Type_Rating_Helper::monthBeginAtByYearAndMonth($year, $month);

				// получаем unix время когда месяц заканчивается
				$month_end_at = Type_Rating_Helper::monthEndAtByYearAndMonth($year, $month);

				$rating        = Gateway_Bus_Company_Rating::getByMonth($event, $month_start_at, $month_end_at, $top_list_offset, $top_list_count);
				$rating->year  = $year;
				$rating->month = $month;
				return $rating;
		}
	}
}