<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_SetDeletedRepostRel extends Struct_Default {

	public string $conversation_map;

	public string $message_map;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $conversation_map, string $message_map):static {

		return new static([
			"conversation_map" => $conversation_map,
			"message_map"      => $message_map,
		]);
	}
}
