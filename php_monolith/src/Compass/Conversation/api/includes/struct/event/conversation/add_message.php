<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_AddMessage extends Struct_Default {

	public string $conversation_map;

	public array $message;

	public array $users;

	public int $conversation_type;

	public string $conversation_name;

	public array $conversation_extra;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $conversation_map, array $message, array $users, int $conversation_type, string $conversation_name, array $conversation_extra):static {

		return new static([
			"conversation_map"   => $conversation_map,
			"message"            => $message,
			"users"              => $users,
			"conversation_type"  => $conversation_type,
			"conversation_name"  => $conversation_name,
			"conversation_extra" => $conversation_extra,
		]);
	}
}
