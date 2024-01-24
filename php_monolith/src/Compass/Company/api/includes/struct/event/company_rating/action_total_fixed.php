<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «Зафиксировано общее число действий пользователей в компании».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_CompanyRating_ActionTotalFixed extends Struct_Default {

	/** @var string чат в который шлем сообщение */
	public string $conversation_map;

	/** @var int год статистики */
	public int $year;

	/** @var int неделя статистики */
	public int $week;

	/** @var int общее число действий */
	public int $count;

	/** @var string имя компании */
	public string $name;

	/**
	 * Статический конструктор.
	 *
	 * @paramint $receiver_user_id
	 *
	 * @throws \parseException
	 */
	public static function build(string $conversation_map, int $year, int $week, int $count, string $name):static {

		return new static([
			"conversation_map" => $conversation_map,
			"year"             => $year,
			"week"             => $week,
			"count"            => $count,
			"name"             => $name,
		]);
	}
}
