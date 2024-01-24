<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «отправка статистики компании за месяц».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_CompanyRating_SendStatisticForMonth extends Struct_Default {

	/** @var int год для рейтинга */
	public int $year;

	/** @var int месяц для рейтинга */
	public int $month;

	/** @var int время следующего выполнения */
	public int $next_time;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $year, int $month, int $next_time):static {

		return new static([
			"year"      => $year,
			"month"     => $month,
			"next_time" => $next_time,
		]);
	}
}
