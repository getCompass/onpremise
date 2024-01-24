<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_DoUnfollowThreadList extends Struct_Default {

	public int $user_id;

	public string $thread_map_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(int $user_id, array $thread_map_list):static {

		return new static([
			"user_id"         => $user_id,
			"thread_map_list" => $thread_map_list,
		]);
	}
}
