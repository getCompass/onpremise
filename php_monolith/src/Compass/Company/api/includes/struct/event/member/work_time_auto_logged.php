<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «Рабочее время было зафиксировано автоматически».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Member_WorkTimeAutoLogged extends Struct_Default {

	/** @var int кому проставили метрику */
	public int $employee_user_id;

	/** @var int кто проставил метрику */
	public int $work_time;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $employee_user_id, int $work_time):static {

		return new static([
			"employee_user_id" => $employee_user_id,
			"work_time"        => $work_time,
		]);
	}
}
