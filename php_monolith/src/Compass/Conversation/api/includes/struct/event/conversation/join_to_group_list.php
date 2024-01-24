<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события вступления в список групп
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_JoinToGroupList extends Struct_Default {

	public int $user_id;

	public array $conversation_map_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, array $conversation_map_list):static {

		return new static([
			"user_id"               => $user_id,
			"conversation_map_list" => $conversation_map_list,
		]);
	}
}
