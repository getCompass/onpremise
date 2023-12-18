<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Базовая структура события «отправки системного сообщения пользователю».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Message_SendSystemMessageListToUser extends Struct_Default {

	/** @var int id бота-отправителя */
	public int $bot_user_id;

	/** @var int id получателя сообщений */
	public int $receiver_user_id;

	/** @var array список сообщений */
	public array $message_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $bot_user_id, int $receiver_user_id, array $message_list):static {

		return new static([
			"bot_user_id"      => $bot_user_id,
			"receiver_user_id" => $receiver_user_id,
			"message_list"     => $message_list,
		]);
	}
}
