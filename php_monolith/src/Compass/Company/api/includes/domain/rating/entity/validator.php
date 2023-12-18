<?php

namespace Compass\Company;

/**
 * Класс для валидации данных вводимых пользователем о рейтинге
 */
class Domain_Rating_Entity_Validator {

	/**
	 * Выбрасываем исключение если передан неккоректный ивент
	 *
	 * @throws cs_RatingIncorrectEvent
	 */
	public static function assertIncorrectEvent(string $event):void {

		if (!in_array($event, Domain_Rating_Entity_Rating::ALLOW_EVENTS)) {
			throw new cs_RatingIncorrectEvent();
		}
	}

	/**
	 * Выбрасываем исключение если передан неккоректный тип периода
	 *
	 * @throws cs_RatingIncorrectPeriodType
	 */
	public static function assertIncorrectPeriodType(int $period_type):void {

		if (!in_array($period_type, Domain_Rating_Entity_Rating::ALLOW_PERIOD_TYPES)) {
			throw new cs_RatingIncorrectPeriodType();
		}
	}

	/**
	 * Выбрасываем исключение если передан неккоректный период
	 *
	 * @throws cs_RatingIncorrectPeriodType
	 * @throws cs_RatingIncorrectPeriod
	 */
	public static function assertIncorrectPeriod(int $company_created_at, int $period_type, int $year, int $month, int $week):void {

		// получаем время когда заканчивается период для рейтинга
		$period_end_at = match ($period_type) {

			Domain_Rating_Entity_Rating::PERIOD_WEEK_TYPE  => Type_Rating_Helper::weekEndAtByYearAndWeek($year, $week),
			Domain_Rating_Entity_Rating::PERIOD_MONTH_TYPE => Type_Rating_Helper::monthEndAtByYearAndMonth($year, $month),
			default                                        => throw new cs_RatingIncorrectPeriodType(),
		};

		// если временная метка когда период заканчивается меньше, чем время создания компании
		if ($period_end_at < $company_created_at) {
			throw new cs_RatingIncorrectPeriod();
		}
	}

	/**
	 * Выбрасываем исключение если передан неккоректные параметры даты
	 *
	 * @throws cs_RatingIncorrectDateParams
	 */
	public static function assertIncorrectDateParams(int $year, int $month, int $week):void {

		if ($year <= 0 || $month <= 0 || $month > 12 || $week <= 0 || $week > 54) {
			throw new cs_RatingIncorrectDateParams();
		}
	}

	/**
	 * Выбрасываем исключение если передан неккоректные параметры даты
	 *
	 * @throws cs_RatingIncorrectYearOrWeeks
	 */
	public static function assertIncorrectYearOrWeeks(int $year, int $start_week, int $end_week):void {

		if ($end_week < $start_week || $year < 0 || $start_week < 1) {
			throw new cs_RatingIncorrectYearOrWeeks();
		}
	}

	/**
	 * Выбрасываем исключение если передан неккоректный лимит
	 *
	 * @throws cs_RatingIncorrectLimit
	 */
	public static function assertIncorrectLimit(int $limit):void {

		if ($limit < 1) {
			throw new cs_RatingIncorrectLimit();
		}
	}

	/**
	 * Выбрасываем исключение если передан неккоректный оффест
	 *
	 * @throws cs_RatingIncorrectOffset
	 */
	public static function assertIncorrectOffset(int $offset):void {

		if ($offset < 0) {
			throw new cs_RatingIncorrectOffset();
		}
	}
}