<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_UpdateConversationAfterUnblock extends Struct_Default {

	public string $conversation_map;

	public int $user_id;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(string $conversation_map, int $user_id):static {

		return new static([
			"conversation_map" => $conversation_map,
			"user_id"          => $user_id,
		]);
	}
}
