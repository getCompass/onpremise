<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «Запрошено текстовое сообщение от системного бота».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Testing_SystemBotTextMessageRequested extends Struct_Default {

	/** @var int кому выслать сообщение */
	public int $user_id;

	/** @var string содержимое сообщения */
	public string $text;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, string $text):static {

		return new static([
			"user_id" => $user_id,
			"text"    => $text,
		]);
	}
}
