<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_TryUnfollowThreadByConversationMap extends Struct_Default {

	public int $user_id;

	public array $conversation_map;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(int $user_id, array $conversation_map):static {

		return new static([
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
		]);
	}
}
