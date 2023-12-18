<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Событие — «Зафиксировано общее число действий пользователей в компании».
 *
 * @event_category company_rating
 * @event_name     action_total_fixed
 */
class Type_Event_CompanyRating_ActionTotalFixed {

	/** @var string тип события */
	public const EVENT_TYPE = "company_rating.action_total_fixed";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $conversation_map, int $year, int $week, int $count, string $name):Struct_Event_Base {

		$event_data = Struct_Event_CompanyRating_ActionTotalFixed::build($conversation_map, $year, $week, $count, $name);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_CompanyRating_ActionTotalFixed {

		return Struct_Event_CompanyRating_ActionTotalFixed::build(...$event["event_data"]);
	}
}
