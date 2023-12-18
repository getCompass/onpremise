<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Task_TaskAddedConversation extends Struct_Default {

	public int $task_type;

	public array $params;

	/**
	 * Статический конструктор.
	 *
	 * @param int   $task_type
	 * @param array $params
	 *
	 * @return Struct_Event_Task_TaskAddedConversation
	 * @throws ParseFatalException
	 */
	public static function build(int $task_type, array $params):static {

		return new static([
			"task_type" => $task_type,
			"params"    => $params,
		]);
	}
}
