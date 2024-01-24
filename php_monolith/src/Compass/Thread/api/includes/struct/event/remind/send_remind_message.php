<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Базовая структура события «отправить сообщение-Напоминание».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Remind_SendRemindMessage extends Struct_Default {

	public int $remind_id;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $remind_id):static {

		return new static([
			"remind_id" => $remind_id,
		]);
	}
}
