<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Базовая структура события «отправки системного сообщения рейтинга пользователю».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Message_SendSystemRatingMessageToUser extends Struct_Default {

	/** @var int id бота-отправителя */
	public int $bot_user_id;

	/** @var int id получателя сообщений */
	public int $receiver_user_id;

	/** @var int год для рейтинга */
	public int $year;

	/** @var int неделя для рейтинга */
	public int $week;

	/** @var int количество для рейтинга */
	public int $count;

	/** @var string имя компании */
	public string $name;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $bot_user_id, int $receiver_user_id, int $year, int $week, int $count, string $name):static {

		return new static([
			"bot_user_id"      => $bot_user_id,
			"receiver_user_id" => $receiver_user_id,
			"year"             => $year,
			"week"             => $week,
			"count"            => $count,
			"name"             => $name,
		]);
	}
}
