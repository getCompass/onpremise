<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_UpdateLeftMenuForUserOnMessageEdit extends Struct_Default {

	public string $conversation_map;

	public array $message;

	public array $users;

	public array $new_mentioned_user_id_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $conversation_map, array $message, array $users, array $new_mentioned_user_id_list):static {

		return new static([
			"conversation_map"           => $conversation_map,
			"message"                    => $message,
			"users"                      => $users,
			"new_mentioned_user_id_list" => $new_mentioned_user_id_list,
		]);
	}
}
