<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «Изменился тип чата оповещения».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Member_SystemBotMoved extends Struct_Default {

	/** @var int кому выслать сообщение */
	public int $user_id;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id):static {

		return new static([
			"user_id" => $user_id,
		]);
	}
}
