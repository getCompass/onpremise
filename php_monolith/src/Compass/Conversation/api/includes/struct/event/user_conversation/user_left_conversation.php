<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Базовая структура события «пользователь покинул диалог».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserConversation_UserLeftConversation extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/** @var string ид диалога */
	public string $conversation_map;

	/** @var int дата покидания диалога */
	public int $left_at;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, string $conversation_map, int $left_at):static {

		return new static([
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
			"left_at"          => $left_at,
		]);
	}
}
