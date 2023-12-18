<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_UpdateUserDataOnMessageAdd extends Struct_Default {

	public int $user_id;

	public string $conversation_map;

	public array $message;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, string $conversation_map, array $message):static {

		return new static([
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
			"message"          => $message,
		]);
	}
}
