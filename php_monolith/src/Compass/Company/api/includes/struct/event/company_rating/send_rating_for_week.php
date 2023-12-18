<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «отправка рейтинга компании за неделю».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_CompanyRating_SendRatingForWeek extends Struct_Default {

	/** @var int год для рейтинга */
	public int $year;

	/** @var int неделя для рейтинга */
	public int $week;

	/** @var int время следующего выполнения */
	public int $next_time;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $year, int $week, int $next_time):static {

		return new static([
			"year"      => $year,
			"week"      => $week,
			"next_time" => $next_time,
		]);
	}
}
