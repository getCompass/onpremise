<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Bot_MessageAdded extends Struct_Default {

	public int $bot_user_id;

	public string $conversation_map;

	public array $message_data;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $bot_user_id, string $conversation_map, array $message_data):static {

		return new static([
			"bot_user_id"      => $bot_user_id,
			"conversation_map" => $conversation_map,
			"message_data"     => $message_data,
		]);
	}
}
