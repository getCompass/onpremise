<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «Зафиксирован рейтинг метрик карточки сотрудника в компании».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_CompanyRating_EmployeeMetricTotalFixed extends Struct_Default {

	/** @var string чат в который шлем сообщение */
	public string $conversation_map;

	/** @var int статистика с */
	public int $period_start_date;

	/** @var int статистика по */
	public int $period_end_date;

	/** @var array список метрик [metric_type, count] */
	public array $metric_count_item_list;

	/** @var string имя компании */
	public string $company_name;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $conversation_map, int $period_start_date, int $period_end_date, array $metric_count_item_list, string $company_name):static {

		return new static([
			"conversation_map"       => $conversation_map,
			"period_start_date"      => $period_start_date,
			"period_end_date"        => $period_end_date,
			"metric_count_item_list" => $metric_count_item_list,
			"company_name"           => $company_name,
		]);
	}
}
