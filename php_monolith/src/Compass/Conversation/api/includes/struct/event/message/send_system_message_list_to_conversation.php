<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Базовая структура события «отправки системного сообщения пользователю».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Message_SendSystemMessageListToConversation extends Struct_Default {

	/** @var int id бота-отправителя */
	public int $bot_user_id;

	/** @var string map чата */
	public string $conversation_map;

	/** @var array список сообщений */
	public array $message_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $bot_user_id, string $conversation_map, array $message_list):static {

		return new static([
			"bot_user_id"      => $bot_user_id,
			"conversation_map" => $conversation_map,
			"message_list"     => $message_list,
		]);
	}
}
