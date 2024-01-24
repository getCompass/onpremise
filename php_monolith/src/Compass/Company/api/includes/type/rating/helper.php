<?php

namespace Compass\Company;

/**
 * Вспомогательные функции для рейтинга
 */
class Type_Rating_Helper {

	/**
	 * получаем номер недели по количеству прошедших дней с начала выбранного года
	 */
	public static function getWeekNumberByDaysCount(int $year, int $day):int {

		// самое начало выбранного года
		$date = new \DateTime("{$year}-01-01");

		// уменьшаем на один количество дней для правильного подсчета
		$day--;

		// устанавливаем количество дней, котороге прошло
		$date->add(new \DateInterval("P{$day}D"));

		// получаем номер недели
		return (int) $date->format("W");
	}

	/**
	 * получаем timestamp конца недели по году и номеру недели
	 */
	public static function weekEndAtByYearAndWeek(int $year, int $week):int {

		$date = new \DateTime();

		// устанавливаем нужную дату
		$date->setISODate($year, $week);

		// получаем последний день недели
		$date->modify("this Sunday 23:59:59");

		// отдаем timestamp
		return $date->format("U");
	}

	/**
	 * получаем timestamp дня, до которого получаем рейтинг
	 */
	public static function getToDateAt(int $year, int $week, int $last_year_week):int {

		// если недель больше, отдаем последний день года
		if ($week >= $last_year_week) {

			$date = new \DateTime("last day of December $year 23:59:59");

			// отдаем timestamp
			return $date->format("U");
		}

		$date = new \DateTime();

		// устанавливаем нужную дату
		$date->setISODate($year, $week);

		// получаем последний день недели
		$date->modify("this Sunday 23:59:59");

		// отдаем timestamp
		return $date->format("U");
	}

	/**
	 * получаем время, с которого получать рейтинг
	 *
	 * Warning!!! Время стоит на 12:00 чтобы случайно не взять unix-время предыдущего дня
	 */
	public static function getFromDateAt(int $year, int $week):int {

		// если первая неделя, берем первый день года
		if ($week <= 1) {

			$date = new \DateTime("first day of January $year 12:00:00");

			// отдаем timestamp
			return $date->format("U");
		}

		$date = new \DateTime();

		// устанавливаем нужную дату
		$date->setISODate($year, $week);

		// получаем первый день недели
		$date->modify("this Monday 12:00");

		// отдаем timestamp
		return $date->format("U");
	}

	/**
	 * получаем unix начала недели по году и номеру недели
	 */
	public static function weekBeginAtByYearAndWeek(int $year, int $week):int {

		$date = new \DateTime();

		// устанавливаем нужную дату
		$date->setISODate($year, $week);

		// получаем первый день недели
		$date->modify("this Monday 12:00");

		// отдаем timestamp
		return $date->format("U");
	}

	/**
	 * получаем конец месяца по году и номеру месяца
	 */
	public static function monthEndAtByYearAndMonth(int $year, int $month):int {

		$date = new \DateTime();

		// устанавливаем нужный год и месяц
		$date->setDate($year, $month, 1);

		// получаем последний день месяца
		$date->modify("last day of this month 23:59:59");

		// отдаем timestamp
		return $date->format("U");
	}

	/**
	 * получаем конец месяца по году и номеру месяца
	 */
	public static function monthBeginAtByYearAndMonth(int $year, int $month):int {

		$date = new \DateTime();

		// устанавливаем нужный год и месяц
		$date->setDate($year, $month, 1);

		// получаем первый день месяца
		$date->modify("first day of this month 00:00");

		// отдаем timestamp
		return $date->format("U");
	}
}