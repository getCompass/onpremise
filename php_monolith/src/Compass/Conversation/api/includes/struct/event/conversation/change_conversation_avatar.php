<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_ChangeConversationAvatar extends Struct_Default {

	public string $conversation_map;

	public string $avatar_file_map;

	public array $users;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $conversation_map, string $avatar_file_map, array $users):static {

		return new static([
			"conversation_map" => $conversation_map,
			"avatar_file_map"  => $avatar_file_map,
			"users"            => $users,
		]);
	}
}
