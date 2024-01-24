<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события «создание компании».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Company_CreateCompany extends Struct_Default {

	public int   $creator_user_id;
	public array $bot_list;
	public int   $is_enabled_employee_card;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $creator_user_id, array $bot_list, int $is_enabled_employee_card):static {

		return new static([
			"creator_user_id"          => $creator_user_id,
			"bot_list"                 => $bot_list,
			"is_enabled_employee_card" => $is_enabled_employee_card,
		]);
	}
}
