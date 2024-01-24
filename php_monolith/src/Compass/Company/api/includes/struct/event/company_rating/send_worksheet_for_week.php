<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «отправка рейтинга рабочих часов за неделю».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_CompanyRating_SendWorksheetForWeek extends Struct_Default {

	/** @var int период начала для рейтинга */
	public int $period_start_date;

	/** @var int период конца для рейтинга */
	public int $period_end_date;

	/** @var int время следующего выполнения */
	public int $next_time;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $period_start_date, int $period_end_date, int $next_time):static {

		return new static([
			"period_start_date" => $period_start_date,
			"period_end_date"   => $period_end_date,
			"next_time"         => $next_time,
		]);
	}
}
