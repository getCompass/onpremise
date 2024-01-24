<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события инициировано покидание диалога.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserConversation_LeaveConversationInitiated extends Struct_Default {

	public int $user_id;

	public string $conversation_map;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, string $conversation_map):static {

		return new static([
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
		]);
	}
}
