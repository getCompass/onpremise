<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Событие — «Зафиксирован рейтинг рабочих часов».
 *
 * @event_category company_rating
 * @event_name     worksheet_rating_fixed
 */
class Type_Event_CompanyRating_WorksheetRatingFixed {

	/** @var string тип события */
	public const EVENT_TYPE = "company_rating.worksheet_rating_fixed";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $conversation_map, int $period_start_date, int $period_end_date, array $leader_user_work_item_list, array $driven_user_work_item_list):Struct_Event_Base {

		$event_data = Struct_Event_CompanyRating_WorksheetRatingFixed::build(
			$conversation_map, $period_start_date, $period_end_date, $leader_user_work_item_list, $driven_user_work_item_list
		);

		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_CompanyRating_WorksheetRatingFixed {

		return Struct_Event_CompanyRating_WorksheetRatingFixed::build(...$event["event_data"]);
	}
}
