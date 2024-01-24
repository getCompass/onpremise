<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Базовый класс для получения рейтинга по периодам
 */
class Domain_Rating_Action_GetEventCountByInterval {

	/**
	 * получение количества ивентов по типам
	 *
	 * @return Struct_Bus_Rating_EventCount[]
	 *
	 * @throws \parseException
	 * @throws paramException
	 * @throws \busException
	 */
	public static function do(int $year, int $start_week, int $end_week, string $event):array {

		// получаем timestamp когда неделя началась
		$from_date_at = Type_Rating_Helper::weekBeginAtByYearAndWeek($year, $start_week);

		// получаем timestamp когда неделя заканчивается
		$to_date_at = Type_Rating_Helper::weekEndAtByYearAndWeek($year, $end_week);
		$to_date_at = $to_date_at < $from_date_at ? $from_date_at : $to_date_at;

		// получаем список эвентов
		$event_count_list = $event == Domain_Rating_Entity_Rating::GENERAL ? Gateway_Bus_Company_Rating::getGeneralEventCountByInterval($year, $from_date_at, $to_date_at) : Gateway_Bus_Company_Rating::getEventCountByInterval($year, $from_date_at, $to_date_at, $event);

		// заполняем пустые недели
		$event_count_list = self::_doFillWithEmptyWeek($event_count_list, $year);

		// сортируем по неделям по возрастанию
		ksort($event_count_list, SORT_NUMERIC);

		return $event_count_list;
	}

	/**
	 * Инициализируем в нули пустые не существующие недели
	 */
	protected static function _doFillWithEmptyWeek(array $event_count_list, int $year):array {

		if (count($event_count_list) < 1) {
			return [];
		}

		// устанавливаем счетчики
		$min_week = 100;
		$max_week = 0;

		// ищем минимальную и максимальную неделю
		foreach ($event_count_list as $v) {

			if ($min_week > $v->week) {
				$min_week = $v->week;
			}

			if ($max_week < $v->week) {
				$max_week = $v->week;
			}
		}

		// докидываем пустые недели если они есть в промежутке
		for ($i = $min_week; $i < $max_week; $i++) {

			if (!isset($event_count_list[$i])) {

				$event_count_list[$i]        = new Struct_Bus_Rating_EventCount();
				$event_count_list[$i]->year  = $year;
				$event_count_list[$i]->week  = (int) $i;
				$event_count_list[$i]->count = 0;
			}
		}

		return $event_count_list;
	}
}