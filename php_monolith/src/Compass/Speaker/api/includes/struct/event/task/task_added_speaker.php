<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Task_TaskAddedSpeaker extends Struct_Default {

	public int $task_type;

	public array $params;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $task_type, array $params):static {

		return new static([
			"task_type" => $task_type,
			"params"    => $params,
		]);
	}
}
