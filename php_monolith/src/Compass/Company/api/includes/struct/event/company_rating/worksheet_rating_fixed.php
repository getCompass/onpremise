<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «Зафиксирован рейтинг рабочих часов в компании».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_CompanyRating_WorksheetRatingFixed extends Struct_Default {

	/** @var string чат куда шлем сообщение */
	public string $conversation_map;

	/** @var int статистика с */
	public int $period_start_date;

	/** @var int статистика по */
	public int $period_end_date;

	/** @var array список лидеров [user_id, work_time] */
	public array $leader_user_work_item_list;

	/** @var array список аутсайдеров [user_id, work_time] */
	public array $driven_user_work_item_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $conversation_map, int $period_start_date, int $period_end_date, array $leader_user_work_item_list, array $driven_user_work_item_list):static {

		return new static([
			"conversation_map"           => $conversation_map,
			"period_start_date"          => $period_start_date,
			"period_end_date"            => $period_end_date,
			"leader_user_work_item_list" => $leader_user_work_item_list,
			"driven_user_work_item_list" => $driven_user_work_item_list,
		]);
	}
}
