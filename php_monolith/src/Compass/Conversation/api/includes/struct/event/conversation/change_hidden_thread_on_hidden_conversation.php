<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_ChangeHiddenThreadOnHiddenConversation extends Struct_Default {

	public int $user_id;

	public array $thread_map_list;

	public bool $need_to_hide_parent_thread;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, array $thread_map_list, bool $need_to_hide_parent_thread):static {

		return new static([
			"user_id"                    => $user_id,
			"thread_map_list"            => $thread_map_list,
			"need_to_hide_parent_thread" => $need_to_hide_parent_thread,
		]);
	}
}
