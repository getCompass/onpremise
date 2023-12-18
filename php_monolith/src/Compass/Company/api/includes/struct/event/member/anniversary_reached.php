<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «У сотрудника годовщина в компании».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Member_AnniversaryReached extends Struct_Default {

	/** @var string чат куда шлем */
	public string $conversation_map;

	/** @var int кому проставили метрику */
	public int $employee_user_id;

	/** @var int[] список редакторов для пользователя */
	public array $editor_user_id_list;

	/** @var int когда нанят сотрудник */
	public int $hired_at;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $conversation_map, int $employee_user_id, array $editor_user_id_list, int $hired_at):static {

		return new static([
			"conversation_map"    => $conversation_map,
			"employee_user_id"    => $employee_user_id,
			"editor_user_id_list" => $editor_user_id_list,
			"hired_at"            => $hired_at,
		]);
	}
}
