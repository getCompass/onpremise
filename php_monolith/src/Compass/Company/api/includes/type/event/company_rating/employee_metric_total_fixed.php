<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Событие — «Зафиксирован рейтинг метрик карточки сотрудника в компании».
 *
 * @event_category company_rating
 * @event_name     employee_metric_total_fixed
 */
class Type_Event_CompanyRating_EmployeeMetricTotalFixed {

	/** @var string тип события */
	public const EVENT_TYPE = "company_rating.employee_metric_total_fixed";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $conversation_map, int $period_start_date, int $period_end_date, array $metric_count_item_list, string $company_name):Struct_Event_Base {

		$event_data = Struct_Event_CompanyRating_EmployeeMetricTotalFixed::build(
			$conversation_map, $period_start_date, $period_end_date, $metric_count_item_list, $company_name
		);

		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_CompanyRating_EmployeeMetricTotalFixed {

		return Struct_Event_CompanyRating_EmployeeMetricTotalFixed::build(...$event["event_data"]);
	}
}
