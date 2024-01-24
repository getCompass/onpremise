<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_ChangeConversationName extends Struct_Default {

	public string $conversation_map;

	public string $conversation_name;

	public array $users;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $conversation_map, string $conversation_name, array $users):static {

		return new static([
			"conversation_map"  => $conversation_map,
			"conversation_name" => $conversation_name,
			"users"             => $users,
		]);
	}
}
